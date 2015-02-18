<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module;

class ModuleInstallerUpgraderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleInstallerUpgraderFactory
     */
    private $moduleInstallerUpgraderFactory;

    protected function setUp()
    {
        $returnValueMap = [
            [
                'Foo\One\Setup\InstallSchema',
                $this->getMockForAbstractClass('Magento\Framework\Setup\InstallSchemaInterface'),
            ],
            [
                'Bar\Two\Setup\UpgradeSchema',
                $this->getMockForAbstractClass('Magento\Framework\Setup\UpgradeSchemaInterface'),
            ],
            [
                'Foo\One\Setup\Recurring',
                $this->getMockForAbstractClass('Magento\Framework\Setup\InstallSchemaInterface'),
            ],
        ];

        $serviceLocatorMock = $this->getMockForAbstractClass('Zend\ServiceManager\ServiceLocatorInterface', ['get']);
        $serviceLocatorMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($returnValueMap));
        $directoryListMock = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $directoryListMock
            ->expects($this->exactly(2))
            ->method('getPath')
            ->will($this->returnValue(__DIR__ . '/_files'));
        $this->moduleInstallerUpgraderFactory = new ModuleInstallerUpgraderFactory(
            $serviceLocatorMock,
            $directoryListMock
        );
    }

    public function testCreateSchemaInstaller()
    {
        $installer = $this->moduleInstallerUpgraderFactory->createSchemaInstaller('Foo_One');
        $this->assertInstanceOf('Magento\Framework\Setup\InstallSchemaInterface', $installer);
    }

    public function testCreateSchemaUpgrader()
    {
        $upgrader = $this->moduleInstallerUpgraderFactory->createSchemaUpgrader('Bar_Two');
        $this->assertInstanceOf('Magento\Framework\Setup\UpgradeSchemaInterface', $upgrader);
    }

    public function testCreateRecurringUpgrader()
    {
        $installer = $this->moduleInstallerUpgraderFactory->createRecurringUpgrader('Foo_One');
        $this->assertInstanceOf('Magento\Framework\Setup\InstallSchemaInterface', $installer);
    }
}