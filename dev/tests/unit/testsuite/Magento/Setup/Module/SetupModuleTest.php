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
            ->expects($this->once())
            ->method('getOne')
            ->with($this->moduleName)
            ->will($this->returnValue(['name' => 'SampleModule', 'schema_version' => '2.0.0']));
        $this->resourceModelMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
    }

    public function testApplyRecurringUpdatesWithValidFile()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);
        $fileResolver
            ->expects($this->once())
            ->method('getSqlSetupFiles')
            ->with($this->moduleName, 'recurring.php')
            ->will($this->returnValue([__DIR__ . '/_files/recurring.php']));
        $this->loggerMock
            ->expects($this->once())
            ->method('log');

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceModelMock);

        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $contextMock
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
            ->expects($this->once())
            ->method('getSqlSetupFiles')
            ->with($this->moduleName, 'recurring.php')
            ->will($this->returnValue([__DIR__ . '/_files/recurring1.php']));
        $this->loggerMock
            ->expects($this->once())
            ->method('log');

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceModelMock);

        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $contextMock
        );
        $setupModule->applyRecurringUpdates();
    }

    public function testApplyUpdatesWithNullResource()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);
        $fileResolver
            ->expects($this->once())
            ->method('getResourceCode')
            ->will($this->returnValue(null));

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceModelMock);

        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $contextMock
        );
        $setupModule = $setupModule->applyUpdates();
        $this->assertInstanceOf('Magento\Setup\Module\SetupModule', $setupModule);
    }

    public function testApplyUpdatesWithNoVersions()
    {
        $fileResolver = $this->getMock('Magento\Setup\Module\Setup\FileResolver', [], [], '', false);

        $contextMock = $this->getMock('\Magento\Framework\Model\Resource\Db\Context', [], [], '', false);
        $contextMock->expects($this->any())->method('getResources')->willReturn($this->resourceModelMock);

        $setupModule = new SetupModule(
            $this->loggerMock,
            $this->moduleListMock,
            $fileResolver,
            $this->moduleName,
            $contextMock
        );
        $setupModule = $setupModule->applyUpdates();
        $this->assertInstanceOf('Magento\Setup\Module\SetupModule', $setupModule);
    }
}
