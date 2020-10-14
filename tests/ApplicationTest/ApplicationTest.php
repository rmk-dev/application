<?php

namespace Rmk\ApplicationTest;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rmk\Application\Application;
use Rmk\Application\Event\ApplicationEvent;
use Rmk\Application\Event\ApplicationInitEvent;
use Rmk\Application\Event\ApplicationShutdownEvent;
use Rmk\Application\Event\EventDispatcherCreatedEvent;
use Rmk\Application\Event\RequestCreatedEvent;
use Rmk\Application\Event\RequestHandlerCreatedEvent;
use Rmk\Application\Event\ResponseCreatedEvent;
use Rmk\Application\Event\RouteMatchedEvent;
use Rmk\Application\Event\RouterCreatedEvent;
use Rmk\Application\Factory\AltoRouterAdapterFactory;
use Rmk\Application\Factory\EventDispatcherFactory;
use Rmk\Application\Factory\ListenerProviderFactory;
use Rmk\Application\Factory\RouterServiceFactory;
use Rmk\Application\Output\OutputInterface;
use Rmk\Application\Output\TextOutput;
use Rmk\CallbackResolver\CallbackResolver;
use Rmk\Router\Adapter\RouterAdapterInterface;
use Rmk\Router\Route;
use Rmk\Router\RouterServiceInterface;
use Rmk\ServiceContainer\InjectionFactory;
use Rmk\ServiceContainer\ServiceContainer;

class TestServiceContainer extends ServiceContainer
{
}

class ApplicationTest extends TestCase
{

    private function getAppConfig(): array
    {
        return [
            ServiceContainer::CONFIG_SERVICES_KEY => [
                'factories' => [
                    EventDispatcherInterface::class => EventDispatcherFactory::class,
                    ListenerProviderInterface::class => ListenerProviderFactory::class,
                    CallbackResolver::class => InjectionFactory::class,
                    RouterAdapterInterface::class => AltoRouterAdapterFactory::class,
                    RouterServiceInterface::class => RouterServiceFactory::class,
                    OutputInterface::class => function () {
                        return new TextOutput();
                    },
                ],
            ],
            'event_listeners' => [
                RouterCreatedEvent::class => [
                    function (RouterCreatedEvent $event) {
                        $this->assertSame($event->getRouter(), $event->getParam('router'));
                    }
                ],
                RouteMatchedEvent::class => [
                    function (RouteMatchedEvent $event) {
                        $this->assertSame($event->getApplication()->getMatchedRoute(), $event->getMatchedRoute());
                    }
                ],
            ],
            'routes' => [
                'home' => [
                    'url' => '/',
                    'handler' => null
                ],
            ],
        ];
    }

    public function testInit(): void
    {
        $applicationListener = static function (ApplicationEvent $event) {
            // ...
        };
        $dispatcherCreatedListener = function (EventDispatcherCreatedEvent $event) {
            $event->getEventDispatcher()->getListenerProvider()->addEventListener(
                ApplicationShutdownEvent::class,
                function (ApplicationShutdownEvent $shutdownEvent) {
                    $this->assertSame($shutdownEvent->getEmitter(), $shutdownEvent->getApplication());
                }
            );
        };
        $handler = new \stdClass();
        $config = $this->getAppConfig();
        $config['event_listeners'] = [
            ApplicationEvent::class => [
                $applicationListener,
            ],
            EventDispatcherCreatedEvent::class => [
                $dispatcherCreatedListener
            ],
            ApplicationInitEvent::class => [
                function (ApplicationInitEvent $event) {
                    $this->assertSame($event->getRouter(), $event->getApplication()->getRouter());
                    $this->assertSame($event->getServiceContainer(), $event->getApplication()->getServiceContainer());
                    $this->assertSame($event->getEventDispatcher(), $event->getApplication()->getEventDispatcher());
                }
            ],
            RequestCreatedEvent::class => [
                function (RequestCreatedEvent $event) {
                    $this->assertEmpty($event->getRequest()->getBody() . '');
                }
            ],
            ResponseCreatedEvent::class => [
                function (ResponseCreatedEvent $event) {
                    $this->assertEquals(200, $event->getResponse()->getStatusCode());
                }
            ],
            RequestHandlerCreatedEvent::class => [
                function (RequestHandlerCreatedEvent $event) {
                    $this->assertSame($event->getApplication()->getRequestHandler(), $event->getRequestHandler());
                }
            ]
        ];
        $config['routes']['home']['handler'] = $handler;

        $application = new Application();
        $application->init($config);
        $route = $application->getRouter()->matchUrl('/');
        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame($handler, $route->getHandler());
    }

    public function testInitWithCustomContainer()
    {
        $config = $this->getAppConfig();
        $container = new TestServiceContainer();
        $config['service_container'] = $container;
        $application = new Application();
        $application->init($config);
        $this->assertSame($config['service_container'], $application->getServiceContainer());
    }

    public function testInitWithCustomContainerClass(): void
    {
        $config = $this->getAppConfig();
        $config['service_container_class'] = TestServiceContainer::class;
        $application = new Application();
        $application->init($config);
        $this->assertInstanceOf(TestServiceContainer::class, $application->getServiceContainer());
    }

    public function testInitWithCustomRequestFactory(): void
    {
        $config = $this->getAppConfig();
        $request = $this->getMockForAbstractClass(ServerRequestInterface::class);
        $config[ServiceContainer::CONFIG_SERVICES_KEY]['factories'][ServerRequestInterface::class] = static function () use ($request) {
            return $request;
        };
        $application = new Application();
        $application->init($config);
        $this->assertSame($request, $application->getRequest());
    }

    public function testInitWithCustomResponseFactory(): void
    {
        $config = $this->getAppConfig();
        $response = $this->getMockForAbstractClass(ResponseInterface::class);
        $config[ServiceContainer::CONFIG_SERVICES_KEY]['factories'][ResponseInterface::class] = static function () use ($response) {
            return $response;
        };
        $application = new Application();
        $application->init($config);
        $this->assertSame($response, $application->getResponse());
    }

    public function testInitWithDefaultEventDispatcherFactory(): void
    {
        $config = $this->getAppConfig();
        unset($config[ServiceContainer::CONFIG_SERVICES_KEY]['factories'][EventDispatcherInterface::class]);
        $application = new Application();
        $application->init($config);
        $this->assertInstanceOf(EventDispatcherInterface::class, $application->getEventDispatcher());
    }

    public function testInitWithDefaultRouterFactory(): void
    {
        $config = $this->getAppConfig();
        unset($config[ServiceContainer::CONFIG_SERVICES_KEY]['factories'][RouterServiceInterface::class]);
        $application = new Application();
        $application->init($config);
        $this->assertInstanceOf(RouterServiceInterface::class, $application->getRouter());
    }

    /**
     * @depends testInit
     */
    public function testRun(): void
    {
        $config = $this->getAppConfig();
        $application = new Application();
        $application->init($config);
        $application->run();
    }

    public function testRunWithDefaultOutputFormatter(): void
    {
        $config = $this->getAppConfig();
        unset($config[ServiceContainer::CONFIG_SERVICES_KEY]['factories'][OutputInterface::class]);
        $application = new Application();
        $application->init($config);
        $application->run();
    }
}
