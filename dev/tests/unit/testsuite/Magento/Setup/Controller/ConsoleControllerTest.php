<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Controller;

use \Magento\Setup\Model\UserConfigurationDataMapper as UserConfig;

class ConsoleControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\ConsoleLogger
     */
    private $consoleLogger;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Lists
     */
    private $options;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Installer
     */
    private $installer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\MaintenanceMode
     */
    private $maintenanceMode;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Mvc\MvcEvent
     */
    private $mvcEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Console\Request
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Stdlib\Parameters
     */
    private $parameters;

    /**
     * @var ConsoleController
     */
    private $controller;

    public function setUp()
    {
        $this->consoleLogger = $this->getMock('\Magento\Setup\Model\ConsoleLogger', [], [], '', false);
        $installerFactory = $this->getMock('\Magento\Setup\Model\InstallerFactory', [], [], '', false);
        $this->installer = $this->getMock('\Magento\Setup\Model\Installer', [], [], '', false);
        $installerFactory->expects($this->once())->method('create')->with($this->consoleLogger)->willReturn(
            $this->installer
        );
        $this->options = $this->getMock('\Magento\Setup\Model\Lists', [], [], '', false);
        $this->maintenanceMode = $this->getMock('\Magento\Framework\App\MaintenanceMode', [], [], '', false);

        $this->request = $this->getMock('\Zend\Console\Request', [], [], '', false);
        $response = $this->getMock('\Zend\Console\Response', [], [], '', false);
        $routeMatch = $this->getMock('\Zend\Mvc\Router\RouteMatch', [], [], '', false);

        $this->parameters= $this->getMock('\Zend\Stdlib\Parameters', [], [], '', false);
        $this->request->expects($this->any())->method('getParams')->willReturn($this->parameters);

        $this->mvcEvent = $this->getMock('\Zend\Mvc\MvcEvent', [], [], '', false);
        $this->mvcEvent->expects($this->once())->method('setRequest')->with($this->request)->willReturn(
            $this->mvcEvent
        );
        $this->mvcEvent->expects($this->once())->method('getRequest')->willReturn($this->request);
        $this->mvcEvent->expects($this->once())->method('setResponse')->with($response)->willReturn($this->mvcEvent);
        $routeMatch->expects($this->any())->method('getParam')->willReturn('not-found');
        $this->mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);

        $this->controller = new ConsoleController(
            $this->consoleLogger, $this->options, $installerFactory, $this->maintenanceMode
        );
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $response);
    }

    public function testGetRouterConfig()
    {
        $controller = $this->controller;
        $actualRoute = $controller::getRouterConfig();
        $this->assertArrayHasKey($controller::CMD_HELP, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_INSTALL, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_INSTALL_CONFIG, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_INSTALL_SCHEMA, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_INSTALL_DATA, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_INSTALL_USER_CONFIG, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_INSTALL_ADMIN_USER, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_UPDATE, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_UNINSTALL, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_UPDATE, $actualRoute);
        $this->assertArrayHasKey($controller::CMD_MAINTENANCE, $actualRoute);
    }

    public function testSetEventManager()
    {
        $controller = $this->controller;
        $closureMock = function () use ($controller) {
        };

        $eventManager = $this->getMock('\Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->atLeastOnce())->method('attach')->will($this->returnCallback($closureMock));
        $returnValue = $this->controller->setEventManager($eventManager);
        $this->assertSame($returnValue, $this->controller);
    }

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Some Message
     */
    public function testSetEventManagerWithError()
    {
        $e = 'Some Message';
        $eventManager = $this->getMock('\Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())->method('attach')->willThrowException(new \RuntimeException($e));
        $returnValue = $this->controller->setEventManager($eventManager);
        $this->assertEquals($returnValue, $this->controller);
    }

    public function testOnDispatch()
    {
        $returnValue = $this->controller->onDispatch($this->mvcEvent);
        $this->assertInstanceOf('\Zend\View\Model\ConsoleModel', $returnValue);
    }

    public function testOnDispatchWithException()
    {
        $errorMessage = 'Missing route matches; unsure how to retrieve action';
        $event = $this->getMock('\Zend\Mvc\MvcEvent');
        $exception = $this->getMock('\Magento\Setup\Exception', [], [$errorMessage]);
        $event->expects($this->once())->method('getRouteMatch')->willThrowException($exception);
        $this->consoleLogger->expects($this->once())->method('log')->with($errorMessage);
        $this->controller->onDispatch($event);
    }

    public function testInstallAction()
    {
        $this->installer->expects($this->once())->method('install')->with($this->parameters);
        $this->controller->installAction();
    }

    public function testInstallDeploymentConfigAction()
    {
        $this->installer->expects($this->once())->method('checkInstallationFilePermissions');
        $this->installer->expects($this->once())->method('installDeploymentConfig')->with($this->parameters);
        $this->controller->installDeploymentConfigAction();
    }

    public function testInstallSchemaAction()
    {
        $this->installer->expects($this->once())->method('installSchema');
        $this->controller->installSchemaAction();
    }

    public function testInstallDataAction()
    {
        $this->installer->expects($this->once())->method('installDataFixtures');
        $this->controller->installDataAction();
    }

    public function testUpdateAction()
    {
        $this->installer->expects($this->once())->method('installSchema');
        $this->installer->expects($this->once())->method('installDataFixtures');
        $this->controller->updateAction();
    }

    public function testInstallUserConfigAction()
    {
        $this->installer->expects($this->once())->method('installUserConfig')->with($this->parameters);
        $this->controller->installUserConfigAction();
    }

    public function testInstallAdminUserAction()
    {
        $this->installer->expects($this->once())->method('installAdminUser')->with($this->parameters);
        $this->controller->installAdminUserAction();
    }

    public function testUninstallAction()
    {
        $this->installer->expects($this->once())->method('uninstall');
        $this->controller->uninstallAction();
    }

    /**
     * @param int $maintenanceMode
     * @param int $setCount
     * @param int $logCount
     *
     * @dataProvider maintenanceActionDataProvider
     */
    public function testMaintenanceAction($maintenanceMode, $setCount, $logCount)
    {
        $mapGetParam = [
            ['set', null, $maintenanceMode],
            ['addresses', null, null],
        ];
        $this->request->expects($this->exactly(2))->method('getParam')->will($this->returnValueMap($mapGetParam));
        $this->maintenanceMode->expects($this->exactly($setCount))->method('set');
        $this->maintenanceMode->expects($this->exactly(0))->method('setAddresses');
        $this->maintenanceMode->expects($this->once())->method('isOn')->willReturn($maintenanceMode);
        $this->maintenanceMode->expects($this->once())->method('getAddressInfo')->willReturn([]);
        $this->consoleLogger->expects($this->exactly($logCount))->method('log');
        $this->controller->maintenanceAction();
    }

    /**
     * @return array
     */
    public function maintenanceActionDataProvider()
    {
        return [
            [1, 1, 2],
            [0, 1, 2],
            [null, 0, 1],
        ];
    }

    /**
     * @param array $addresses
     * @param int $logCount
     * @param int $setAddressesCount
     *
     * @dataProvider maintenanceActionWithAddressDataProvider
     */
    public function testMaintenanceActionWithAddress($addresses, $logCount, $setAddressesCount)
    {
        $mapGetParam = [
            ['set', null, true],
            ['addresses', null, $addresses],
        ];
        $this->request->expects($this->exactly(2))->method('getParam')->will($this->returnValueMap($mapGetParam));
        $this->maintenanceMode->expects($this->exactly(1))->method('set');
        $this->maintenanceMode->expects($this->exactly($setAddressesCount))->method('setAddresses');
        $this->maintenanceMode->expects($this->once())->method('isOn')->willReturn(true);
        $this->maintenanceMode->expects($this->once())->method('getAddressInfo')->willReturn($addresses);
        $this->consoleLogger->expects($this->exactly($logCount))->method('log');
        $this->controller->maintenanceAction();
    }

    /**
     * @return array
     */
    public function maintenanceActionWithAddressDataProvider()
    {
        return [
            [['address1', 'address2'], 3, 1],
            [[], 2, 1],
            [null, 2, 0],
        ];
    }

    /**
     * @param string $type
     * @param string $method
     * @param array $expectedValue
     *
     * @dataProvider helpActionForLanguageCurrencyTimezoneDataProvider
     */
    public function testHelpActionForLanguageCurrencyTimezone($type, $method, $expectedValue)
    {
        $this->request->expects($this->once())->method('getParam')->willReturn($type);
        $this->options->expects($this->once())->method($method)->willReturn($expectedValue);
        $returnValue = $this->controller->helpAction();

        //Need to convert from String to Associated Array.
        $result = explode(PHP_EOL, trim($returnValue));
        foreach ($result as $value) {
            $tempArray  = explode(' => ' , $value);
            $actual[$tempArray[0]] = $tempArray[1];
        }

        $this->assertSame($expectedValue, $actual);
    }

    /**
     * @return array
     */
    public function helpActionForLanguageCurrencyTimezoneDataProvider()
    {
        return [
            [UserConfig::KEY_LANGUAGE, 'getLocaleList', [
                    'en_GB' => 'English (United Kingdom)',
                    'en_US' => 'English (United States)'
                ]
            ],
            [UserConfig::KEY_CURRENCY, 'getCurrencyList', [
                    'USD' => 'US Dollar (USD)',
                    'EUR' => 'Euro (EUR)'
                ]
            ],
            [UserConfig::KEY_TIMEZONE, 'getTimezoneList', [
                    'America/Chicago' => 'Central Standard Time (America/Chicago)',
                    'America/Mexico_City' => 'Central Standard Time (Mexico) (America/Mexico_City)'
                ]
            ],
        ];
    }

    public function testHelpActionNoType()
    {
        $beginHelpString = "\n==-------------------==\n"
            . "   Magento Setup CLI   \n"
            . "==-------------------==\n";
        $this->request->expects($this->once())->method('getParam')->willReturn(false);
        $returnValue = $this->controller->helpAction();
        $this->assertStringStartsWith($beginHelpString, $returnValue);
    }

    /**
     * @param string $option
     * @param string $noParameters
     *
     * @dataProvider helpActionDataProvider
     */
    public function testHelpAction($option, $noParameters)
    {
        $this->request->expects($this->once())->method('getParam')->willReturn($option);
        
        $usage = $this->controller->getCommandUsage();
        $expectedValue = explode(' ', (strlen($usage[$option]) > 0 ? $usage[$option] : $noParameters));
        $returnValue = explode(
            ' ', trim(str_replace([PHP_EOL, 'Available parameters:'], '', $this->controller->helpAction()))
        );
        $expectedValue = asort($expectedValue);
        $returnValue = asort($returnValue);
        $this->assertEquals($expectedValue, $returnValue);
    }

    /**
     * @return array
     */
    public function helpActionDataProvider()
    {
        $noParameters = 'This command has no parameters.';
        return [
            ['install',''],
            ['update', $noParameters],
            ['uninstall', $noParameters],
            ['install-configuration', ''],
            ['install-schema', $noParameters],
            ['install-data', $noParameters],
            ['install-user-configuration', ''],
            ['install-admin-user', ''],
            ['maintenance', ''],
            ['help', ''],
        ];
    }
}
