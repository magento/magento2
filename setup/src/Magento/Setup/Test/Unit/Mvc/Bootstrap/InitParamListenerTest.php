<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Mvc\Bootstrap;

use Laminas\Console\Request;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\SharedEventManager;
use Laminas\Http\Headers;
use Laminas\Http\Response;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\RequestInterface;
use \Magento\Setup\Mvc\Bootstrap\InitParamListener;
use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InitParamListenerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var InitParamListener
     */
    private $listener;

    /** callable[][] */
    private $callbacks = [];

    protected function setUp()
    {
        $this->listener = new InitParamListener();
    }

    public function testAttach()
    {
        $events = $this->prepareEventManager();
        $this->listener->attach($events);
    }

    public function testDetach()
    {
        $events = $this->prepareEventManager();
        $this->listener->attach($events);
        $events->expects($this->once())->method('detach')->with([$this->listener, 'onBootstrap'])->willReturn(true);
        $this->listener->detach($events);
    }

    public function testOnBootstrap()
    {
        /** @var MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->createMock(MvcEvent::class);
        $mvcApplication = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->once())->method('getApplication')->willReturn($mvcApplication);
        $serviceManager = $this->createMock(ServiceManager::class);
        $initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS][DirectoryList::ROOT] = ['path' => '/test'];
        $serviceManager->expects($this->once())->method('get')
            ->willReturn($initParams);
        $serviceManager->expects($this->exactly(2))->method('setService')
            ->withConsecutive(
                [
                    \Magento\Framework\App\Filesystem\DirectoryList::class,
                    $this->isInstanceOf(\Magento\Framework\App\Filesystem\DirectoryList::class),
                ],
                [
                    \Magento\Framework\Filesystem::class,
                    $this->isInstanceOf(\Magento\Framework\Filesystem::class),
                ]
            );
        $mvcApplication->expects($this->any())->method('getServiceManager')->willReturn($serviceManager);

        $eventManager = $this->getMockForAbstractClass(EventManagerInterface::class);
        $mvcApplication->expects($this->any())->method('getEventManager')->willReturn($eventManager);
        $eventManager->expects($this->any())->method('attach');

        $this->listener->onBootstrap($mvcEvent);
    }

    public function testCreateDirectoryList()
    {
        $initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS] =
            [DirectoryList::ROOT => [DirectoryList::PATH => '/test/root']];

        $directoryList = $this->listener->createDirectoryList($initParams);
        $this->assertEquals('/test/root/app', $directoryList->getPath(DirectoryList::APP));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Magento root directory is not specified.
     */
    public function testCreateDirectoryListException()
    {
        $this->listener->createDirectoryList([]);
    }

    public function testCreateServiceNotConsole()
    {
        /**
         * @var ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject $serviceLocator
         */
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $mvcApplication = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $request = $this->createMock(RequestInterface::class);
        $mvcApplication->expects($this->any())->method('getRequest')->willReturn($request);
        $serviceLocator->expects($this->once())->method('get')->with('Application')
            ->willReturn($mvcApplication);
        $this->assertEquals([], $this->listener->createService($serviceLocator));
    }

    /**
     * @param array $zfAppConfig Data that comes from Zend Framework Application config
     * @param array $env Config that comes from SetEnv
     * @param string $cliParam Parameter string
     * @param array $expectedArray Expected result array
     *
     * @dataProvider createServiceDataProvider
     */
    public function testCreateService($zfAppConfig, $env, $cliParam, $expectedArray)
    {
        foreach ($env as $envKey => $envValue) {
            $_SERVER[$envKey] = $envValue;
        }
        $listener = new InitParamListener();
        /**
         * @var ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject $serviceLocator
         */
        $serviceLocator = $this->createMock(ServiceLocatorInterface::class);
        $mvcApplication = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $request->expects($this->any())
            ->method('getContent')
            ->willReturn(
                $cliParam ? ['install', '--magento-init-params=' . $cliParam] : ['install']
            );
        $mvcApplication->expects($this->any())->method('getConfig')->willReturn(
            $zfAppConfig ? [InitParamListener::BOOTSTRAP_PARAM => $zfAppConfig] : []
        );

        $mvcApplication->expects($this->any())->method('getRequest')->willReturn($request);
        $serviceLocator->expects($this->once())->method('get')->with('Application')
            ->willReturn($mvcApplication);

        $this->assertEquals($expectedArray, $listener->createService($serviceLocator));
    }

    /**
     * @return array
     */
    public function createServiceDataProvider()
    {
        return [
            'none' => [[], [], '', []],
            'mage_mode App' => [['MAGE_MODE' => 'developer'], [], '', ['MAGE_MODE' => 'developer']],
            'mage_mode Env' => [[], ['MAGE_MODE' => 'developer'], '', ['MAGE_MODE' => 'developer']],
            'mage_mode CLI' => [[], [], 'MAGE_MODE=developer', ['MAGE_MODE' => 'developer']],
            'one MAGE_DIRS CLI' => [
                [],
                [],
                'MAGE_MODE=developer&MAGE_DIRS[base][path]=/var/www/magento2',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2']], 'MAGE_MODE' => 'developer'],
            ],
            'two MAGE_DIRS CLI' => [
                [],
                [],
                'MAGE_MODE=developer&MAGE_DIRS[base][path]=/var/www/magento2&MAGE_DIRS[cache][path]=/tmp/cache',
                [
                    'MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2'], 'cache' => ['path' => '/tmp/cache']],
                    'MAGE_MODE' => 'developer',
                ],
            ],
            'mage_mode only' => [[], [], 'MAGE_MODE=developer', ['MAGE_MODE' => 'developer']],
            'MAGE_DIRS Env' => [
                [],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2']], 'MAGE_MODE' => 'developer'],
                '',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2']], 'MAGE_MODE' => 'developer'],
            ],
            'two MAGE_DIRS' => [
                [],
                [],
                'MAGE_MODE=developer&MAGE_DIRS[base][path]=/var/www/magento2&MAGE_DIRS[cache][path]=/tmp/cache',
                [
                    'MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2'], 'cache' => ['path' => '/tmp/cache']],
                    'MAGE_MODE' => 'developer',
                ],
            ],
            'Env overwrites App' => [
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/App']], 'MAGE_MODE' => 'developer'],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']], 'MAGE_MODE' => 'developer'],
                '',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']], 'MAGE_MODE' => 'developer'],
            ],
            'CLI overwrites Env' => [
                ['MAGE_MODE' => 'developer'],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']]],
                'MAGE_DIRS[base][path]=/var/www/magento2/CLI',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/CLI']], 'MAGE_MODE' => 'developer'],
            ],
            'CLI overwrites All' => [
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/App']], 'MAGE_MODE' => 'production'],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']]],
                'MAGE_DIRS[base][path]=/var/www/magento2/CLI',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/CLI']], 'MAGE_MODE' => 'developer'],
            ],
        ];
    }

    public function testCreateFilesystem()
    {
        $testPath = 'test/path/';

        /**
         * @var \Magento\Framework\App\Filesystem\DirectoryList|
         * \PHPUnit_Framework_MockObject_MockObject $directoryList
         */
        $directoryList = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()->getMock();
        $directoryList->expects($this->any())->method('getPath')->willReturn($testPath);
        $filesystem = $this->listener->createFilesystem($directoryList);

        // Verifies the filesystem was created with the directory list passed in
        $this->assertEquals($testPath, $filesystem->getDirectoryRead('app')->getAbsolutePath());
    }

    /**
     * Prepare the event manager with a SharedEventManager, it will expect attach() to be called once.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareEventManager()
    {
        $this->callbacks[] =  [$this->listener, 'onBootstrap'];

        /** @var EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject $events */
        $eventManager = $this->createMock(EventManagerInterface::class);

        $sharedManager = $this->createMock(SharedEventManager::class);
        $sharedManager->expects($this->once())->method('attach')->with(
            Application::class,
            MvcEvent::EVENT_BOOTSTRAP,
            [$this->listener, 'onBootstrap']
        );

        $sharedManager->expects($this->once())->method('getListeners')->willReturn($this->callbacks);
        $eventManager->expects($this->once())->method('getSharedManager')->willReturn($sharedManager);

        return $eventManager;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testAuthPreDispatch()
    {
        $cookiePath = 'test';
        $eventMock = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeMatchMock = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $applicationMock = $this->getMockBuilder(Application::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManagerMock = $this->getMockBuilder(ServiceManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $deploymentConfigMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $deploymentConfigMock->expects($this->once())
            ->method('isAvailable')
            ->willReturn(true);
        $omProvider = $this->getMockBuilder(\Magento\Setup\Model\ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock = $this->getMockForAbstractClass(\Magento\Framework\ObjectManagerInterface::class);
        $adminAppStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sessionConfigMock = $this->getMockBuilder(\Magento\Backend\Model\Session\AdminConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendAppListMock = $this->getMockBuilder(\Magento\Backend\App\BackendAppList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $backendAppMock = $this->getMockBuilder(\Magento\Backend\App\BackendApp::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlMock = $this->getMockBuilder(\Magento\Backend\Model\Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $authenticationMock = $this->getMockBuilder(\Magento\Backend\Model\Auth::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adminSessionMock = $this->getMockBuilder(\Magento\Backend\Model\Auth\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();
        $headersMock = $this->getMockBuilder(Headers::class)
            ->disableOriginalConstructor()
            ->getMock();

        $routeMatchMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    [
                        'controller',
                        null,
                        'testController'
                    ],
                    [
                        'action',
                        null,
                        'testAction'
                    ]
                ]
            );
        $eventMock->expects($this->once())
            ->method('getRouteMatch')
            ->willReturn($routeMatchMock);
        $eventMock->expects($this->once())
            ->method('getApplication')
            ->willReturn($applicationMock);
        $serviceManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [
                        \Magento\Framework\App\DeploymentConfig::class,
                        true,
                        $deploymentConfigMock,
                    ],
                    [
                        \Magento\Setup\Model\ObjectManagerProvider::class,
                        true,
                        $omProvider,
                    ],
                ]
            );
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [
                        \Magento\Framework\App\State::class,
                        $adminAppStateMock,
                    ],
                    [
                        \Magento\Backend\Model\Session\AdminConfig::class,
                        $sessionConfigMock,
                    ],
                    [
                        \Magento\Backend\App\BackendAppList::class,
                        $backendAppListMock,
                    ],
                    [
                        \Magento\Backend\Model\Auth::class,
                        $authenticationMock,
                    ],
                ]
            );
        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [
                        \Magento\Backend\Model\Auth\Session::class,
                        [
                            'sessionConfig' => $sessionConfigMock,
                            'appState' => $adminAppStateMock
                        ],
                        $adminSessionMock,
                    ],
                    [
                        \Magento\Backend\Model\Url::class,
                        [],
                        $urlMock,
                    ],
                ]
            );
        $omProvider->expects($this->once())
            ->method('get')
            ->willReturn($objectManagerMock);
        $adminAppStateMock->expects($this->once())
            ->method('setAreaCode')
            ->with(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $applicationMock->expects($this->once())
            ->method('getServiceManager')
            ->willReturn($serviceManagerMock);
        $backendAppMock->expects($this->once())
            ->method('getCookiePath')
            ->willReturn($cookiePath);
        $urlMock->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn('http://base-url/');
        $sessionConfigMock->expects($this->once())
            ->method('setCookiePath')
            ->with('/' . $cookiePath);
        $backendAppListMock->expects($this->once())
            ->method('getBackendApp')
            ->willReturn($backendAppMock);
        $authenticationMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $adminSessionMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Backend::setup_wizard', null)
            ->willReturn(false);
        $adminSessionMock->expects($this->once())
            ->method('destroy');
        $eventMock->expects($this->once())
            ->method('getResponse')
            ->willReturn($responseMock);
        $responseMock->expects($this->once())
            ->method('getHeaders')
            ->willReturn($headersMock);
        $headersMock->expects($this->once())
            ->method('addHeaderLine');
        $responseMock->expects($this->once())
            ->method('setStatusCode')
            ->with(302);
        $eventMock->expects($this->once())
            ->method('stopPropagation');

        $this->assertSame(
            $this->listener->authPreDispatch($eventMock),
            $responseMock
        );
    }

    public function testAuthPreDispatchSkip()
    {
        $eventMock = $this->getMockBuilder(MvcEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $routeMatchMock = $this->getMockBuilder(RouteMatch::class)
            ->disableOriginalConstructor()
            ->getMock();
        $deploymentConfigMock = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $deploymentConfigMock->expects($this->never())
            ->method('isAvailable');
        $routeMatchMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap(
                [
                    [
                        'controller',
                        null,
                        \Magento\Setup\Controller\Session::class
                    ],
                    [
                        'action',
                        null,
                        'unlogin'
                    ]
                ]
            );
        $eventMock->expects($this->once())
            ->method('getRouteMatch')
            ->willReturn($routeMatchMock);
        $eventMock->expects($this->never())
            ->method('getApplication');

        $this->assertSame(
            $this->listener->authPreDispatch($eventMock),
            false
        );
    }
}
