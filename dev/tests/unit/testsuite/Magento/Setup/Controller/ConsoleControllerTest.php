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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

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
        $routeMatch->expects($this->any())->method('getParam')->willReturn('install');
        $this->mvcEvent->expects($this->any())->method('getRouteMatch')->willReturn($routeMatch);

        $this->objectManager = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerFactory = $this->getMock('Magento\Setup\Model\ObjectManagerFactory', [], [], '', false);
        $objectManagerFactory->expects($this->any())->method('create')->willReturn($this->objectManager);

        $this->controller = new ConsoleController(
            $this->consoleLogger,
            $this->options,
            $installerFactory,
            $this->maintenanceMode,
            $objectManagerFactory,
            $objectManagerFactory
        );
        $this->controller->setEvent($this->mvcEvent);
        $this->controller->dispatch($this->request, $response);
    }

    public function testSetEventManager()
    {
        $controllerMock = $this->controller;
        $closureMock = function () use ($controllerMock){
        };
        $eventManager = $this->getMock('\Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->any())->method('attach')->will($this->returnCallback($closureMock));
        $returnValue = $this->controller->setEventManager($eventManager);
        $this->assertEquals($returnValue, $this->controller);
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
        $this->controller->onDispatch($this->mvcEvent);
    }

    /**
     * @expectedException        \Zend\Mvc\Exception\DomainException
     * @expectedExceptionMessage Missing route matches; unsure how to retrieve action
     */
    public function testOnDispatchWithException ()
    {
        $e = new \Zend\Mvc\Exception\DomainException('Missing route matches; unsure how to retrieve action');
        $event = $this->getMock('\Zend\Mvc\MvcEvent');
        $this->controller->onDispatch($event);
        $this->consoleLogger->expects($this->once())->method('log')->with($e->getMessage());
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
     * @param array $addresses
     * @param int $setCount
     * @param int $logCount
     *
     * @dataProvider maintenanceActionDataProvider
     */
    public function testMaintenanceAction($maintenanceMode, $addresses, $setCount, $logCount)
    {
        $mapGetParam = [
            ['set', null, $maintenanceMode],
            ['addresses', null, $addresses],
        ];
        $this->request->expects($this->exactly(2))->method('getParam')->will($this->returnValueMap($mapGetParam));
        $this->maintenanceMode->expects($this->exactly($setCount))->method('set');
        $expected = $addresses !== null ? 1 : 0;
        $this->maintenanceMode->expects($this->exactly($expected))->method('setAddresses');
        $this->maintenanceMode->expects($this->once())->method('isOn')->willReturn($maintenanceMode);
        $this->maintenanceMode->expects($this->once())->method('getAddressInfo')->willReturn($addresses);
        $this->consoleLogger->expects($this->exactly($logCount))->method('log');
        $this->controller->maintenanceAction();
    }

    /**
     * @return array
     */
    public function maintenanceActionDataProvider()
    {
        return [
            [1, ['address1', 'address2'], 1, 3],
            [0, [], 1, 2],
            [null, ['address1'], 0, 2],
            [1, null, 1, 2],
            [0, null, 1, 2],
        ];
    }

    /**
     * @param string $type
     * @param string $method
     * @param array $value
     *
     * @dataProvider helpActionForLanguageCurrencyTimezoneDataProvider
     */
    public function testHelpActionForLanguageCurrencyTimezone($type, $method, $value)
    {
        $this->request->expects($this->once())->method('getParam')->willReturn($type);
        $this->options->expects($this->once())->method($method)->willReturn($value);
        $this->controller->helpAction();
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

    public function testHelpActionForModuleList()
    {
        $this->request->expects($this->once())->method('getParam')->willReturn(ConsoleController::HELP_LIST_OF_MODULES);
        $moduleListMock = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $moduleListMock
            ->expects($this->once())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Core', 'Magento_Store']));
        $fullModuleListMock = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $fullModuleListMock
            ->expects($this->once())
            ->method('getNames')
            ->will($this->returnValue(['Magento_Core', 'Magento_Store', 'Magento_Directory']));
        $returnValueMap = [
            [
                'Magento\Framework\Module\ModuleList',
                [],
                $moduleListMock,
            ],
            [
                'Magento\Framework\Module\FullModuleList',
                [],
                $fullModuleListMock,
            ],
        ];
        $this->objectManager->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($returnValueMap));
        $this->controller->helpAction();
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
     * @param string $command
     * @param string $modules
     * @param bool $isForce
     * @param bool $expectedIsEnabled
     * @param string[] $expectedModules
     * @dataProvider moduleActionDataProvider
     */
    public function testModuleAction($command, $modules, $isForce, $expectedIsEnabled, $expectedModules)
    {
        $status = $this->getModuleActionMocks($command, $modules, $isForce, false);
        $status->expects($this->once())->method('getUnchangedModules')->willReturn([]);
        if (!$isForce) {
            $status->expects($this->once())->method('checkConstraints')->willReturn([]);
        }
        $status->expects($this->once())
            ->method('setIsEnabled')
            ->with($expectedIsEnabled, $expectedModules);
        $this->consoleLogger->expects($this->once())->method('log');
        $this->controller->moduleAction();
    }

    /**
     * @return array
     */
    public function moduleActionDataProvider()
    {
        return [
            [ConsoleController::CMD_MODULE_ENABLE, 'Module_Foo,Module_Bar', false, true, ['Module_Foo', 'Module_Bar']],
            [ConsoleController::CMD_MODULE_ENABLE, 'Module_Foo,Module_Bar', true, true, ['Module_Foo', 'Module_Bar']],
            [ConsoleController::CMD_MODULE_DISABLE, 'Module_Foo', false, false, ['Module_Foo']],
            [ConsoleController::CMD_MODULE_DISABLE, 'Module_Bar', true, false, ['Module_Bar']],
        ];
    }

    /**
     * @param string $command
     * @param string $modules
     * @param bool $isForce
     * @param bool $expectedIsEnabled
     * @param string[] $expectedModules
     * @dataProvider moduleActionDataProvider
     */
    public function testModuleActionNoChanges($command, $modules, $isForce, $expectedIsEnabled, $expectedModules)
    {
        $status = $this->getModuleActionMocks($command, $modules, $isForce, true);
        $status->expects($this->once())
            ->method('getUnchangedModules')
            ->with($expectedIsEnabled, $expectedModules)
            ->willReturn($expectedModules);
        $status->expects($this->never())->method('checkConstraints');
        $status->expects($this->never())->method('setIsEnabled');
        $this->consoleLogger->expects($this->once())->method('log');
        $this->controller->moduleAction();
    }

    /**
     * @param string $command
     * @param string $modules
     * @param bool $isForce
     * @param bool $expectedIsEnabled
     * @param string[] $expectedModules
     * @param string[] $unchangedModules
     * @dataProvider moduleActionPartialNoChangesDataProvider
     */
    public function testModuleActionPartialNoChanges(
        $command,
        $modules,
        $isForce,
        $expectedIsEnabled,
        $expectedModules,
        $unchangedModules
    ) {
        $status = $this->getModuleActionMocks($command, $modules, $isForce, false);
        $status->expects($this->once())->method('getUnchangedModules')->willReturn($unchangedModules);
        if (!$isForce) {
            $status->expects($this->once())->method('checkConstraints')->willReturn([]);
        }
        $status->expects($this->once())
            ->method('setIsEnabled')
            ->with($expectedIsEnabled, array_diff($expectedModules, $unchangedModules));
        $this->consoleLogger->expects($this->once())->method('log');
        $this->controller->moduleAction();
    }

    /**
     * @return array
     */
    public function moduleActionPartialNoChangesDataProvider()
    {
        return [
            [
                ConsoleController::CMD_MODULE_ENABLE,
                'Module_Foo,Module_Bar',
                false,
                true,
                ['Module_Foo', 'Module_Bar'],
                ['Module_Foo'],
            ],
            [
                ConsoleController::CMD_MODULE_ENABLE,
                'Module_Foo,Module_Bar',
                true,
                true,
                ['Module_Foo', 'Module_Bar'],
                ['Module_Foo'],
            ],
            [
                ConsoleController::CMD_MODULE_DISABLE,
                'Module_Foo,Module_Bar',
                false,
                false,
                ['Module_Foo', 'Module_Bar'],
                ['Module_Foo'],
            ],
            [
                ConsoleController::CMD_MODULE_DISABLE,
                'Module_Foo,Module_Bar',
                true,
                false,
                ['Module_Foo', 'Module_Bar'],
                ['Module_Foo'],
            ],
        ];
    }

    /**
     * Prepares a set of mocks for testing module action
     *
     * @param string $command
     * @param string $modules
     * @param bool $isForce
     * @param bool $isUnchanged
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getModuleActionMocks($command, $modules, $isForce, $isUnchanged)
    {
        $this->request->expects($this->at(0))->method('getParam')->with(0)->willReturn($command);
        $this->request->expects($this->at(1))->method('getParam')->with('modules')->willReturn($modules);
        if (!$isUnchanged) {
            $this->request->expects($this->at(2))->method('getParam')->with('force')->willReturn($isForce);
        }
        $status = $this->getMock('Magento\Framework\Module\Status', [], [], '', false);
        $this->objectManager->expects($this->once())
            ->method('create')
            ->will($this->returnValue($status));
        return $status;
    }

    /**
     * @expectedException \Magento\Setup\Exception
     * @expectedExceptionMessage Unable to change status of modules because of the following constraints:
     */
    public function testModuleActionNotAllowed()
    {
        $status = $this->getModuleActionMocks(
            ConsoleController::CMD_MODULE_ENABLE,
            'Module_Foo,Module_Bar',
            false,
            false
        );
        $status->expects($this->once())->method('getUnchangedModules')->willReturn([]);
        $status->expects($this->once())
            ->method('checkConstraints')
            ->willReturn(['Circular dependency of Foo and Bar']);
        $status->expects($this->never())->method('setIsEnabled');
        $this->controller->moduleAction();
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
        $expectedValue = explode(' ', (strlen($usage[$option])>0 ? $usage[$option] : $noParameters));
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
