<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Mvc\Bootstrap;

use \Magento\Setup\Mvc\Bootstrap\InitParamListener;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\Mvc\MvcEvent;

/**
 * Tests Magento\Setup\Mvc\Bootstrap\InitParamListener
 */
class InitParamListenerTest extends \PHPUnit_Framework_TestCase
{

    /** @var  InitParamListener */
    private $listener;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $callbackHandler;

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
        $events->expects($this->once())->method('detach')->with($this->callbackHandler)->willReturn(true);
        $this->listener->detach($events);
    }

    public function testOnBootstrap()
    {
        /** @var \Zend\Mvc\MvcEvent|\PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMock('Zend\Mvc\MvcEvent');
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->once())->method('getApplication')->willReturn($mvcApplication);
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');
        $initParams[AppBootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS][DirectoryList::ROOT] = ['path' => '/test'];
        $serviceManager->expects($this->once())->method('get')
            ->willReturn($initParams);
        $serviceManager->expects($this->exactly(2))->method('setService')
            ->withConsecutive(
                ['Magento\Framework\App\Filesystem\DirectoryList',
                 $this->isInstanceOf('Magento\Framework\App\Filesystem\DirectoryList')],
                ['Magento\Framework\Filesystem', $this->isInstanceOf('Magento\Framework\Filesystem')]
            );
        $mvcApplication->expects($this->any())->method('getServiceManager')->willReturn($serviceManager);

        $eventManager = $this->getMockForAbstractClass('Zend\EventManager\EventManagerInterface');
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
         * @var \Zend\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject $serviceLocator
         */
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $request = $this->getMock('Zend\Stdlib\RequestInterface');
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
         * @var \Zend\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject $serviceLocator
         */
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->any())
            ->method('getContent')
            ->willReturn(
                $cliParam ? ['install', '--magento-init-params=' . $cliParam ] : ['install']
            );
        $mvcApplication->expects($this->any())->method('getConfig')->willReturn(
            $zfAppConfig ? [InitParamListener::BOOTSTRAP_PARAM => $zfAppConfig]:[]
        );

        $mvcApplication->expects($this->any())->method('getRequest')->willReturn($request);
        $serviceLocator->expects($this->once())->method('get')->with('Application')
            ->willReturn($mvcApplication);

        $this->assertEquals($expectedArray, $listener->createService($serviceLocator));
    }

    public function createServiceDataProvider()
    {
        return [
            'none' => [[], [], '', []],
            'mage_mode App' => [['MAGE_MODE' => 'developer'], [], '', ['MAGE_MODE' => 'developer']],
            'mage_mode Env' => [[], ['MAGE_MODE' => 'developer'], '', ['MAGE_MODE' => 'developer']],
            'mage_mode CLI' => [[], [], 'MAGE_MODE=developer', ['MAGE_MODE' => 'developer']],
            'one MAGE_DIRS CLI' => [[], [], 'MAGE_MODE=developer&MAGE_DIRS[base][path]=/var/www/magento2',
                       ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2']], 'MAGE_MODE' => 'developer']],
            'two MAGE_DIRS CLI' => [
                [],
                [],
                'MAGE_MODE=developer&MAGE_DIRS[base][path]=/var/www/magento2&MAGE_DIRS[cache][path]=/tmp/cache',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2'], 'cache' => ['path' => '/tmp/cache']],
                 'MAGE_MODE' => 'developer']],
            'mage_mode only' => [[], [], 'MAGE_MODE=developer', ['MAGE_MODE' => 'developer']],
            'MAGE_DIRS Env' => [
                [],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2']], 'MAGE_MODE' => 'developer'],
                '',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2']], 'MAGE_MODE' => 'developer']],
            'two MAGE_DIRS' => [
                [],
                [],
                'MAGE_MODE=developer&MAGE_DIRS[base][path]=/var/www/magento2&MAGE_DIRS[cache][path]=/tmp/cache',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2'], 'cache' => ['path' => '/tmp/cache']],
                 'MAGE_MODE' => 'developer']],
            'Env overwrites App' => [
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/App']], 'MAGE_MODE' => 'developer'],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']], 'MAGE_MODE' => 'developer'],
                '',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']], 'MAGE_MODE' => 'developer']],
            'CLI overwrites Env' => [
                ['MAGE_MODE' => 'developerApp'],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']]],
                'MAGE_DIRS[base][path]=/var/www/magento2/CLI',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/CLI']], 'MAGE_MODE' => 'developerApp']],
            'CLI overwrites All' => [
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/App']], 'MAGE_MODE' => 'production'],
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/Env']]],
                'MAGE_DIRS[base][path]=/var/www/magento2/CLI',
                ['MAGE_DIRS' => ['base' => ['path' => '/var/www/magento2/CLI']], 'MAGE_MODE' => 'production']],
        ];
    }

    public function testCreateFilesystem()
    {
        $testPath = 'test/path/';

        /**
         * @var \Magento\Framework\App\Filesystem\DirectoryList|
         * \PHPUnit_Framework_MockObject_MockObject $directoryList
         */
        $directoryList = $this->getMockBuilder('Magento\Framework\App\Filesystem\DirectoryList')
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
        $this->callbackHandler = $this->getMockBuilder('Zend\Stdlib\CallbackHandler')->disableOriginalConstructor()
            ->getMock();

        /** @var \Zend\EventManager\EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject $events */
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $sharedManager = $this->getMock('Zend\EventManager\SharedEventManager');
        $sharedManager->expects($this->once())->method('attach')->with(
            'Zend\Mvc\Application',
            MvcEvent::EVENT_BOOTSTRAP,
            [$this->listener, 'onBootstrap']
        )->willReturn($this->callbackHandler);
        $eventManager->expects($this->once())->method('getSharedManager')->willReturn($sharedManager);
        return $eventManager;
    }
}
