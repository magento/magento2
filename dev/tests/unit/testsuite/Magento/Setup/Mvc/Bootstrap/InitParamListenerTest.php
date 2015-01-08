<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Mvc\Bootstrap;

use Magento\Framework\App\Bootstrap as AppBootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Zend\Mvc\MvcEvent;

/**
 * Tests Magento\Setup\Mvc\Bootstrap\InitParamListener
 */
class InitParamListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testAttachToConsoleRoutesEmpty()
    {
        $inputConfig = [];
        $expectedConfig = [];
        $this->assertEquals(
            $expectedConfig,
            InitParamListener::attachToConsoleRoutes($inputConfig)
        );
    }

    public function testAttachToConsoleRoutesOneRoute()
    {
        $inputConfig = [
            'console' => ['router' => ['routes' => [['options' => ['route' =>'one_route']]]]]
        ];
        $expectedConfig = [
            'console' => ['router' => ['routes' => [['options' => ['route' => 'one_route [--magento_init_params=]']]]]]
        ];

        $this->assertEquals(
            $expectedConfig,
            InitParamListener::attachToConsoleRoutes($inputConfig)
        );
    }

    public function testAttachToConsoleRoutesManyRoute()
    {
        $inputConfig = [
            'console' => ['router' => ['routes' => [
                ['options' => ['route' =>'one_route']],
                ['options' => ['route' =>'two_route']],
                ['options' => ['route' =>'three_route']],
                ['options' => ['route' =>'four_route']],
                ['options' => ['route' =>'five_route']],
            ]]]
        ];
        $expectedConfig = [
            'console' => ['router' => ['routes' => [
                ['options' => ['route' => 'one_route [--magento_init_params=]']],
                ['options' => ['route' =>'two_route [--magento_init_params=]']],
                ['options' => ['route' =>'three_route [--magento_init_params=]']],
                ['options' => ['route' =>'four_route [--magento_init_params=]']],
                ['options' => ['route' =>'five_route [--magento_init_params=]']],
            ]]]
        ];

        $this->assertEquals(
            $expectedConfig,
            InitParamListener::attachToConsoleRoutes($inputConfig)
        );
    }

    public function testGetConsoleUsage()
    {
        $usage = InitParamListener::getConsoleUsage();
        // First element should be blank line
        $this->assertEquals('', $usage[0]);
        // Only one parameter definition is added to usage statement
        $this->assertContains('[--magento_init_params="<query>"]', $usage[1][0]);
    }

    public function testAttach()
    {
        $listener = new InitParamListener();
        /** @var \Zend\EventManager\EventManagerInterface | \PHPUnit_Framework_MockObject_MockObject $events */
        $events = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')->getMock();
        $sharedManager = $this->getMockBuilder('Zend\EventManager\SharedEventManager')->getMock();
        $sharedManager->expects($this->once())->method('attach')->with(
            'Zend\Mvc\Application',
            MvcEvent::EVENT_BOOTSTRAP,
            [$listener, 'onBootstrap']
        );
        $events->expects($this->once())->method('getSharedManager')->willReturn($sharedManager);

        $listener->attach($events);
    }

    public function testDetach()
    {
        $callbackHandler = $this->getMockBuilder('Zend\Stdlib\CallbackHandler')->disableOriginalConstructor()
            ->getMock();

        $listener = new InitParamListener();
        /** @var \Zend\EventManager\EventManagerInterface | \PHPUnit_Framework_MockObject_MockObject $events */
        $events = $this->getMockBuilder('Zend\EventManager\EventManagerInterface')->getMock();
        $sharedManager = $this->getMockBuilder('Zend\EventManager\SharedEventManager')->getMock();
        $sharedManager->expects($this->once())->method('attach')
            ->with('Zend\Mvc\Application', MvcEvent::EVENT_BOOTSTRAP, [$listener, 'onBootstrap'])
            ->willReturn($callbackHandler);
        $events->expects($this->once())->method('getSharedManager')->willReturn($sharedManager);
        $events->expects($this->once())->method('detach')->with($callbackHandler)->willReturn(true);
        $listener->attach($events);
        $listener->detach($events);
    }

    public function testOnBootstrap()
    {
        $listener = new InitParamListener();
        /** @var \Zend\Mvc\MvcEvent | \PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->getMock();
        /** @var \Zend\Mvc\Application | \PHPUnit_Framework_MockObject_MockObject $mvcApplication */
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->once())->method('getApplication')->willReturn($mvcApplication);
        /** @var \Zend\ServiceManager\ServiceManager | \PHPUnit_Framework_MockObject_MockObject $mvcApplication */
        $serviceManager = $this->getMockBuilder('Zend\ServiceManager\ServiceManager')->getMock();
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

        $listener->onBootstrap($mvcEvent);
    }

    /**
     * @expectedException LogicException
     * @expectedExceptionMessage Magento root directory is not specified.
     */
    public function testOnBootstrapException()
    {
        $listener = new InitParamListener();
        /** @var \Zend\Mvc\MvcEvent | \PHPUnit_Framework_MockObject_MockObject $mvcEvent */
        $mvcEvent = $this->getMockBuilder('Zend\Mvc\MvcEvent')->getMock();
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $mvcEvent->expects($this->once())->method('getApplication')->willReturn($mvcApplication);
        $serviceManager = $this->getMockBuilder('Zend\ServiceManager\ServiceManager')->getMock();
        $mvcApplication->expects($this->any())->method('getServiceManager')->willReturn($serviceManager);
        $listener->onBootstrap($mvcEvent);
    }

    public function testCreateServiceNotConsole()
    {
        $listener = new InitParamListener();
        /**
         * @var \Zend\ServiceManager\ServiceLocatorInterface | \PHPUnit_Framework_MockObject_MockObject $serviceLocator
         */
        $serviceLocator = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')->getMock();
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Stdlib\RequestInterface')->getMock();
        $mvcApplication->expects($this->any())->method('getRequest')->willReturn($request);
        $serviceLocator->expects($this->once())->method('get')->with('Application')
            ->willReturn($mvcApplication);
        $listener->createService($serviceLocator);
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
         * @var \Zend\ServiceManager\ServiceLocatorInterface | \PHPUnit_Framework_MockObject_MockObject $serviceLocator
         */
        $serviceLocator = $this->getMockBuilder('Zend\ServiceManager\ServiceLocatorInterface')->getMock();
        $mvcApplication = $this->getMockBuilder('Zend\Mvc\Application')->disableOriginalConstructor()->getMock();
        $request = $this->getMockBuilder('Zend\Console\Request')->disableOriginalConstructor()->getMock();
        $request->expects($this->any())
            ->method('getContent')
            ->willReturn(
                $cliParam ? ['install', '--magento_init_params=' . $cliParam ] : ['install']
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
}
