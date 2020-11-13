<?php

/**
 * Main application class
 */

namespace Rmk\Application;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rmk\Application\Event\ApplicationInitEvent;
use Rmk\Application\Event\ApplicationOutput;
use Rmk\Application\Event\ApplicationShutdownEvent;
use Rmk\Application\Event\BootstrapEvent;
use Rmk\Application\Event\EventDispatcherCreatedEvent;
use Rmk\Application\Event\RequestCreatedEvent;
use Rmk\Application\Event\RequestHandlerCreatedEvent;
use Rmk\Application\Event\ResponseCreatedEvent;
use Rmk\Application\Event\RouteMatchedEvent;
use Rmk\Application\Event\RouterCreatedEvent;
use Rmk\Application\Factory\AltoRouterAdapterFactory;
use Rmk\Application\Factory\EventDispatcherFactory;
use Rmk\Application\Factory\ListenerProviderFactory;
use Rmk\Application\Factory\RequestFactory;
use Rmk\Application\Factory\RequestHandlerFactory;
use Rmk\Application\Factory\ResponseFactory;
use Rmk\Application\Factory\RouterServiceFactory;
use Rmk\Application\Output\DefaultOutput;
use Rmk\Application\Output\OutputInterface;
use Rmk\Router\Adapter\RouterAdapterInterface;
use Rmk\Router\Route;
use Rmk\Router\RouterServiceInterface;
use Rmk\ServiceContainer\ServiceContainer;
use Rmk\ServiceContainer\ServiceContainerInterface;

/**
 * Class Application
 *
 * @package Rmk\Application
 */
class Application
{

    /**
     * @var ServiceContainerInterface
     */
    protected ServiceContainerInterface $serviceContainer;

    /**
     * @var EventDispatcherInterface
     */
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @var RouterServiceInterface
     */
    protected RouterServiceInterface $router;

    /**
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $response;

    /**
     * @var RequestHandlerInterface
     */
    protected RequestHandlerInterface $requestHandler;

    /**
     * @var bool
     */
    protected bool $initialized = false;

    /**
     * @var Route
     */
    protected Route $matchedRoute;

    /**
     * @var OutputInterface
     */
    protected OutputInterface $outputFormatter;

    /**
     * Initialize the application and its main services
     *
     * @param array $config
     */
    public function init(array $config): void
    {
        $this->initServiceContainer($config);
        $this->initEventDispatcher();
        $this->initRouter();
        $this->initRequest();
        $this->initResponse();

        $this->isInitialized(true);
        $event = new ApplicationInitEvent($this, [
            'service_container' => $this->getServiceContainer(),
            'event_dispatcher' => $this->getEventDispatcher(),
            'router' => $this->getRouter(),
            'request' => $this->getRequest(),
            'response' => $this->getResponse(),
            'initialized' => $this->isInitialized(),
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->serviceContainer = $event->getServiceContainer();
        $this->eventDispatcher = $event->getEventDispatcher();
        $this->isInitialized($event->getInitialized());
        $this->router = $event->getRouter();
        $this->request = $event->getRequest();
        $this->response = $event->getResponse();
    }

    /**
     * Run the application
     *
     * This method matches the requested route and runs the provided request handler
     */
    public function run(): void
    {
        if ($this->isInitialized()) {
            $this->initOutputFormatter();
            $this->matchRoute();
            $this->initRequestHandler();
            $this->getEventDispatcher()->dispatch(new BootstrapEvent($this, [
                'service_container' => $this->getServiceContainer(),
                'event_dispatcher' => $this->getEventDispatcher(),
                'router' => $this->getRouter(),
                'request' => $this->getRequest(),
                'response' => $this->getResponse(),
                'initialized' => $this->isInitialized(),
            ]));
            $this->response = $this->getRequestHandler()->handle($this->request);
        }
        $this->outputResponse();
        $this->getEventDispatcher()->dispatch(new ApplicationShutdownEvent($this));
    }

    /**
     * Initialize service container with provided application configuration
     *
     * @param array $config
     *
     * @return mixed
     */
    protected function initServiceContainer(array $config)
    {
        if (array_key_exists('service_container', $config)) {
            $this->setServiceContainer($config['service_container']);
        } elseif (
            array_key_exists('service_container_class', $config) &&
            class_exists($config['service_container_class'])
        ) {
            $config['service_container'] = new $config['service_container_class']();
            unset($config['service_container_class']);
            return $this->initServiceContainer($config);
        } else {
            $this->setServiceContainer(new ServiceContainer());
        }

        $this->serviceContainer->init($config);
        $this->serviceContainer->add($this->serviceContainer, ContainerInterface::class);
        $this->serviceContainer->add($this, static::class);
    }

    /**
     * Initialize the event dispatcher
     */
    protected function initEventDispatcher(): void
    {
        if (!$this->getServiceContainer()->has(EventDispatcherInterface::class)) {
            $this->getServiceContainer()->addFactory(ListenerProviderFactory::class, ListenerProviderInterface::class);
            $this->getServiceContainer()->addFactory(EventDispatcherFactory::class, EventDispatcherInterface::class);
        }
        $this->setEventDispatcher($this->getServiceContainer()->get(EventDispatcherInterface::class));
        $event = new EventDispatcherCreatedEvent($this, [
            'event_dispatcher' => $this->eventDispatcher
        ]);
        $this->eventDispatcher->dispatch($event);
        $this->setEventDispatcher($event->getEventDispatcher());
    }

    /**
     * Initialize the application router
     */
    protected function initRouter(): void
    {
        if (!$this->getServiceContainer()->has(RouterServiceInterface::class)) {
            $this->getServiceContainer()->addFactory(AltoRouterAdapterFactory::class, RouterAdapterInterface::class);
            $this->getServiceContainer()->addFactory(RouterServiceFactory::class, RouterServiceInterface::class);
        }
        $this->setRouter($this->getServiceContainer()->get(RouterServiceInterface::class));
        $event = new RouterCreatedEvent($this, [
            'router' => $this->router,
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->setRouter($event->getRouter());
    }

    /**
     * Initialize server request
     */
    protected function initRequest(): void
    {
        if (!$this->getServiceContainer()->has(ServerRequestInterface::class)) {
            $this->getServiceContainer()->addFactory(RequestFactory::class, ServerRequestInterface::class);
        }
        $this->setRequest($this->getServiceContainer()->get(ServerRequestInterface::class));
        $event = new RequestCreatedEvent($this, [
            'request' => $this->getRequest()
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->setRequest($event->getRequest());
    }

    /**
     * Initialize response object
     */
    protected function initResponse(): void
    {
        if (!$this->getServiceContainer()->has(ResponseInterface::class)) {
            $this->getServiceContainer()->addFactory(ResponseFactory::class, ResponseInterface::class);
        }
        $this->setResponse($this->getServiceContainer()->get(ResponseInterface::class));
        $event = new ResponseCreatedEvent($this, [
            'response' => $this->getResponse()
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->setResponse($event->getResponse());
    }

    /**
     * Initialize the request handler
     */
    public function initRequestHandler(): void
    {
        if (!$this->getServiceContainer()->has(RequestHandlerInterface::class)) {
            $this->getServiceContainer()->addFactory(RequestHandlerFactory::class, RequestHandlerInterface::class);
        }
        $this->setRequestHandler($this->getServiceContainer()->get(RequestHandlerInterface::class));
        $event = new RequestHandlerCreatedEvent($this, [
            'request_handler' => $this->getRequestHandler(),
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->setRequestHandler($event->getRequestHandler());
    }

    /**
     * Initialize output formatter object
     */
    protected function initOutputFormatter(): void
    {
        if ($this->getServiceContainer()->has(OutputInterface::class)) {
            $this->setOutputFormatter($this->getServiceContainer()->get(OutputInterface::class));
        } else {
            $this->setOutputFormatter(new DefaultOutput());
        }
    }

    /**
     * Match current requested route
     */
    protected function matchRoute(): void
    {
        $this->setMatchedRoute($this->getRouter()->match($this->getRequest()));
        $event = new RouteMatchedEvent($this, [
            'matched_route' => $this->getMatchedRoute(),
        ]);
        $this->getEventDispatcher()->dispatch($event);
        $this->setMatchedRoute($event->getMatchedRoute());
    }

    /**
     * Output the response
     */
    protected function outputResponse(): void
    {
        $event = new ApplicationOutput($this, ['response' => $this->getResponse()]);
        $this->getEventDispatcher()->dispatch($event);
        $this->setResponse($event->getResponse());
        $this->outputFormatter->output($this->getResponse());
    }

    /**
     * @return ServiceContainerInterface
     */
    public function getServiceContainer(): ServiceContainerInterface
    {
        return $this->serviceContainer;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    /**
     * @return RouterServiceInterface
     */
    public function getRouter(): RouterServiceInterface
    {
        return $this->router;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return RequestHandlerInterface
     */
    public function getRequestHandler(): RequestHandlerInterface
    {
        return $this->requestHandler;
    }

    /**
     * @param bool|null $isInitialized
     *
     * @return bool
     */
    public function isInitialized(bool $isInitialized = null): bool
    {
        if ($isInitialized !== null) {
            $this->initialized = $isInitialized;
        }

        return $this->initialized;
    }

    /**
     * @return Route
     */
    public function getMatchedRoute(): Route
    {
        return $this->matchedRoute;
    }

    /**
     * @param ServiceContainerInterface $serviceContainer
     * @return Application
     */
    public function setServiceContainer(ServiceContainerInterface $serviceContainer): Application
    {
        $this->serviceContainer = $serviceContainer;
        return $this;
    }

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @return Application
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): Application
    {
        $this->eventDispatcher = $eventDispatcher;
        return $this;
    }

    /**
     * @param RouterServiceInterface $router
     * @return Application
     */
    public function setRouter(RouterServiceInterface $router): Application
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Application
     */
    public function setRequest(ServerRequestInterface $request): Application
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param ResponseInterface $response
     * @return Application
     */
    public function setResponse(ResponseInterface $response): Application
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param RequestHandlerInterface $requestHandler
     * @return Application
     */
    public function setRequestHandler(RequestHandlerInterface $requestHandler): Application
    {
        $this->requestHandler = $requestHandler;
        return $this;
    }

    /**
     * @param Route $matchedRoute
     * @return Application
     */
    public function setMatchedRoute(Route $matchedRoute): Application
    {
        $this->matchedRoute = $matchedRoute;
        return $this;
    }

    /**
     * @param OutputInterface $outputFormatter
     * @return Application
     */
    public function setOutputFormatter(OutputInterface $outputFormatter): Application
    {
        $this->outputFormatter = $outputFormatter;
        return $this;
    }
}
