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

    protected function setUp(): void
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
        /** @var MvcEvent|\PHPUnit\Framework\MockObject\MockObject $mvcEvent */
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
     */
    public function testCreateDirectoryListException()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Magento root directory is not specified.');

        $this->listener->createDirectoryList([]);
    }

    public function testCreateServiceNotConsole()
    {
        /**
         * @var ServiceLocatorInterface|\PHPUnit\Framework\MockObject\MockObject $serviceLocator
         */
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
        $mvcApplication = $this->getMockBuilder(Application::class)->disableOriginalConstructor()->getMock();
        $request = $this->getMockForAbstractClass(RequestInterface::class);
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
         * @var ServiceLocatorInterface|\PHPUnit\Framework\MockObject\MockObject $serviceLocator
         */
        $serviceLocator = $this->getMockForAbstractClass(ServiceLocatorInterface::class);
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
         * \PHPUnit\Framework\MockObject\MockObject $directoryList
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
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function prepareEventManager()
    {
        $this->callbacks[] =  [$this->listener, 'onBootstrap'];

        /** @var EventManagerInterface|\PHPUnit\Framework\MockObject\MockObject $events */
        $eventManager = $this->getMockForAbstractClass(EventManagerInterface::class);

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
}
