<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Updater;

class SetupFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryList;

    protected function setUp()
    {
        $this->directoryList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $this->directoryList
            ->expects($this->exactly(2))
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/_files'));
    }

    public function testCreateInstaller()
    {
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock
            ->expects($this->any())
            ->method('create')
            ->with('Foo\One\Setup\InstallData')
            ->will($this->returnValue($this->getMockForAbstractClass('Magento\Framework\Setup\InstallDataInterface')));
        $model = new SetupFactory(
            $objectManagerMock,
            $this->directoryList
        );
        $installer = $model->create('Foo_One', 'install');
        $this->assertInstanceOf('Magento\Framework\Setup\InstallDataInterface', $installer);
    }

    public function testCreateUpgrader()
    {
        $objectManagerMock = $this->getMockForAbstractClass('Magento\Framework\ObjectManagerInterface');
        $objectManagerMock
            ->expects($this->any())
            ->method('create')
            ->with('Bar\Two\Setup\UpgradeData')
            ->will($this->returnValue($this->getMockForAbstractClass('Magento\Framework\Setup\UpgradeDataInterface')));
        $model = new SetupFactory(
            $objectManagerMock,
            $this->directoryList
        );
        $upgrader = $model->create('Bar_Two', 'upgrade');
        $this->assertInstanceOf('Magento\Framework\Setup\UpgradeDataInterface', $upgrader);
    }
}
