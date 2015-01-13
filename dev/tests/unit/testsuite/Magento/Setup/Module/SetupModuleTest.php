<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

class SetupModuleTest extends \PHPUnit_Framework_TestCase
{
    const CONNECTION_NAME = 'connection';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\LoggerInterface
     */
    private $loggerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleListInterface
     */
    private $moduleListMock;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Resource
     */
    private $resourceModelMock;

    protected function setUp()
    {
        $this->loggerMock = $this->getMockForAbstractClass('Magento\Setup\Model\LoggerInterface');
        $this->moduleListMock = $this->getMockForAbstractClass('Magento\Framework\Module\ModuleListInterface');
        $this->moduleName = 'SampleModule';
        $this->moduleListMock
            ->expects($this->any())
            ->method('getOne')
            ->with($this->moduleName)
            ->will($this->returnValue(['name' => 'SampleModule', 'schema_version' => '2.0.0']));
        $this->resourceModelMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
    }

    public function testApplyRecurringUpdatesWithValidFile()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);
        $fileResolver
            ->expects($this->any())
            ->method('getSqlSetupFiles')
            ->with($this->moduleName, 'recurring.php')
            ->will($this->returnValue([__DIR__ . '/_files/recurring.php']));
        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $this->resourceModelMock
        );
        $setupModule = $setupModule->applyRecurringUpdates();
        $this->assertInstanceOf('Magento\Setup\Module\SetupModule', $setupModule);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessageRegExp #Error in file: *#
     */
    public function testApplyRecurringUpdatesWithInvalidFile()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);
        $fileResolver
            ->expects($this->any())
            ->method('getSqlSetupFiles')
            ->with($this->moduleName, 'recurring.php')
            ->will($this->returnValue([__DIR__ . '/_files/recurring1.php']));
        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $this->resourceModelMock
        );
        $setupModule->applyRecurringUpdates();
    }

    public  function testApplyUpdatesWithNullResource()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);
        $fileResolver
            ->expects($this->any())
            ->method('getResourceCode')
            ->will($this->returnValue(null));
        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $this->resourceModelMock
        );
        $setupModule = $setupModule->applyUpdates();
        $this->assertInstanceOf('Magento\Setup\Module\SetupModule', $setupModule);
    }

    public  function testApplyUpdatesWithNoVersions()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);
        $this->resourceModelMock
            ->expects($this->any())
            ->method('getDbVersion')
            ->will($this->returnValue(false));
        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $this->resourceModelMock
        );
        $setupModule = $setupModule->applyUpdates();
        $this->assertInstanceOf('Magento\Setup\Module\SetupModule', $setupModule);
    }
}
