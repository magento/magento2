<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\AppInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $application;

    /**
     * @var \Magento\Framework\App\ObjectManagerFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerFactory;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Logger | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dirs;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configDir;

    /**
     * @var MaintenanceMode | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $maintenanceMode;

    /**
     * @var \Magento\Framework\App\Bootstrap | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $bootstrapMock;

    public function setUp()
    {
        $this->objectManagerFactory = $this->getMock('\Magento\Framework\App\ObjectManagerFactory', [], [], '', false);
        $this->objectManager = $this->getMockForAbstractClass('\Magento\Framework\ObjectManager');
        $this->dirs = $this->getMock('\Magento\Framework\App\Filesystem\DirectoryList', ['getDir'], [], '', false);
        $this->maintenanceMode = $this->getMock('\Magento\Framework\App\MaintenanceMode', ['isOn'], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\App\Filesystem', [], [], '', false);

        $this->logger = $this->getMock('Magento\Framework\Logger', [], [], '', false);

        $mapObjectManager = [
            ['Magento\Framework\App\Filesystem\DirectoryList', $this->dirs],
            ['Magento\Framework\App\MaintenanceMode', $this->maintenanceMode],
            ['Magento\Framework\App\Filesystem', $filesystem],
            ['Magento\Framework\Logger', $this->logger]
        ];

        $this->objectManager->expects($this->any())->method('get')
            ->will(($this->returnValueMap($mapObjectManager)));

        $this->configDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');

        $filesystem->expects($this->any())->method('getDirectoryRead')
            ->will(($this->returnValue($this->configDir)));

        $this->application = $this->getMockForAbstractClass('\Magento\Framework\AppInterface');

        $this->objectManager->expects($this->any())->method('create')
            ->will(($this->returnValue($this->application)));

        $this->objectManagerFactory->expects($this->any())->method('create')
            ->will(($this->returnValue($this->objectManager)));

        $this->bootstrapMock = $this->getMock('\Magento\Framework\App\Bootstrap',
            ['assertMaintenance', 'assertInstalled', 'getIsExpected', 'isInstalled', 'terminate'],
            [$this->objectManagerFactory, '', ['value1', 'value2']]
        );
    }

    public function testGetParams()
    {
        $testParams = ['testValue1', 'testValue2'];
        $bootstrap = self::createBootstrap($testParams);
        $this->assertSame($testParams, $bootstrap->getParams());
    }

    /**
     * Creates a bootstrap object
     *
     * @param array $testParams
     * @return Bootstrap
     */
    private function createBootstrap($testParams = ['value1', 'value2'])
    {
        return new Bootstrap($this->objectManagerFactory, '', $testParams);
    }

    public function testCreateApplication()
    {
        $bootstrap = self::createBootstrap();
        $testArgs = ['arg1', 'arg2'];
        $this->assertSame($this->application, $bootstrap->createApplication('someApplicationType', $testArgs));
    }

    public function testGetObjectManager()
    {
        $bootstrap = self::createBootstrap();
        $this->assertSame($this->objectManager, $bootstrap->getObjectManager());
    }

    public function testGetDirList()
    {
        $bootstrap = self::createBootstrap();
        $this->assertSame($this->dirs, $bootstrap->getDirList());
    }

    public function testIsDeveloperMode()
    {
        $bootstrap = self::createBootstrap();
        $this->assertFalse($bootstrap->isDeveloperMode());
        $testParams = [State::PARAM_MODE => State::MODE_DEVELOPER];
        $bootstrap = self::createBootstrap($testParams);
        $this->assertTrue($bootstrap->isDeveloperMode());
    }

    public function testRunNoErrors()
    {
        $responseMock = $this->getMockForAbstractClass('\Magento\Framework\App\ResponseInterface');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')->will($this->returnValue(null));
        $this->bootstrapMock->expects($this->once())->method('assertInstalled')->will($this->returnValue(null));
        $this->application->expects($this->once())->method('launch')->willReturn($responseMock);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithMaintenanceErrors()
    {
        $expectedException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')
            ->will($this->throwException($expectedException));
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithInstallErrors()
    {
        $expectedException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')->will($this->returnValue(null));
        $this->bootstrapMock->expects($this->once())->method('assertInstalled')
            ->will($this->throwException($expectedException));
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    public function testRunWithBothErrors()
    {
        $expectedMaintenanceException = new \Exception('');
        $this->bootstrapMock->expects($this->once())->method('assertMaintenance')
            ->will($this->throwException($expectedMaintenanceException));
        $this->bootstrapMock->expects($this->never())->method('assertInstalled');
        $this->bootstrapMock->expects($this->once())->method('terminate')->with($expectedMaintenanceException);
        $this->application->expects($this->once())->method('catchException')->willReturn(false);
        $this->bootstrapMock->run($this->application);
    }

    /**
     * @param bool $isOn
     * @param bool $isExpected
     *
     * @dataProvider assertMaintenanceDataProvider
     */
    public function testAssertMaintenance($isOn, $isExpected)
    {
        $bootstrap = self::createBootstrap([Bootstrap::PARAM_REQUIRE_MAINTENANCE => $isExpected]);
        $this->maintenanceMode->expects($this->once())->method('isOn')->willReturn($isOn);
        $this->application->expects($this->never())->method('launch');
        $this->application->expects($this->once())->method('catchException')->willReturn(true);
        $bootstrap->run($this->application);
        $this->assertEquals(Bootstrap::ERR_MAINTENANCE, $bootstrap->getErrorCode());
    }

    /**
     * @return array
     */
    public function assertMaintenanceDataProvider()
    {
        return [
            [true, false],
            [false, true]
        ];
    }

    /**
     * @param bool $isInstalled
     * @param bool $isExpected
     *
     * @dataProvider assertInstalledDataProvider
     */
    public function testAssertInstalled($isInstalled, $isExpected)
    {
        $bootstrap = self::createBootstrap([Bootstrap::PARAM_REQUIRE_IS_INSTALLED => $isExpected]);
        $this->configDir->expects($this->once())->method('isExist')->willReturn($isInstalled);
        $this->application->expects($this->never())->method('launch');
        $this->application->expects($this->once())->method('catchException')->willReturn(true);
        $bootstrap->run($this->application);
        $this->assertEquals(Bootstrap::ERR_IS_INSTALLED, $bootstrap->getErrorCode());
    }

    /**
     * @return array
     */
    public function assertInstalledDataProvider()
    {
        return [
            [false, true],
            [true, false],
        ];
    }
}