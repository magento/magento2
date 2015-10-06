<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Dir;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read as DirectoryRead;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    private $vendorModuleName = 'Vendor_Example';
    
    /**
     * @var Reader
     */
    private $moduleDirReader;

    /**
     * @var DirectoryRead|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryReadMock;

    /**
     * @var FileIteratorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesIteratorFactoryMock;

    /**
     * @return DirectoryRead|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createDirectoryReadMock()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $factory = $objectManager->get(\Magento\Framework\Filesystem\File\ReadFactory::class);
        $driver = $objectManager->get(\Magento\Framework\Filesystem\Driver\File::class);
        $directoryReadMock = $this->getMock(
            DirectoryRead::class,
            ['isExist'],
            [$factory, $driver, BP . '/app/code']
        );
        return $directoryReadMock;
    }
    
    /**
     * @param string[] $existingFiles
     */
    private function mockFilesExist(array $existingFiles)
    {
        $this->directoryReadMock->expects($this->any())->method('isExist')
            ->willReturnCallback(function ($file) use ($existingFiles) {
                return in_array($file, $existingFiles, true);
            });
    }

    protected function setUp()
    {
        $appCodeModuleName = 'Magento_Example';
        \Magento\Framework\Module\Registrar::registerModule($this->vendorModuleName, BP . '/vendor/example/example');
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

        /** @var ModuleListInterface|\PHPUnit_Framework_MockObject_MockObject $moduleListMock */
        $moduleListMock = $this->getMock(ModuleListInterface::class);
        $moduleListMock->expects($this->any())->method('getNames')->willReturn(
            [$appCodeModuleName, $this->vendorModuleName]
        );

        $this->directoryReadMock = $this->createDirectoryReadMock();
        /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject $filesystemMock */
        $filesystemMock = $this->getMock(Filesystem::class, [], [], '', false);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')
            ->with(DirectoryList::MODULES)
            ->willReturn($this->directoryReadMock);

        $this->filesIteratorFactoryMock = $this->getMock(FileIteratorFactory::class, [], [], '', false);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->moduleDirReader = new Reader(
            $objectManager->get(\Magento\Framework\Module\Dir::class),
            $moduleListMock,
            $filesystemMock,
            $this->filesIteratorFactoryMock
        );
    }

    protected function tearDown()
    {
        \Magento\Framework\Module\Registrar::unregisterModule($this->vendorModuleName);
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testItIncludesConfigurationFilesFromModulesInVendor()
    {
        $existingFiles = ['Magento/Example/etc/di.xml', '../../vendor/example/example/etc/di.xml'];
        $this->mockFilesExist($existingFiles);

        $this->filesIteratorFactoryMock->expects($this->once())->method('create')
            ->with($this->directoryReadMock, $existingFiles);

        $this->moduleDirReader->getConfigurationFiles('di.xml');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testItIncludesComposerFilesFromModulesInVendor()
    {
        $existingFiles = ['Magento/Example/composer.json', '../../vendor/example/example/composer.json'];
        $this->mockFilesExist($existingFiles);

        $this->filesIteratorFactoryMock->expects($this->once())->method('create')
            ->with($this->directoryReadMock, $existingFiles);
        
        $this->moduleDirReader->getComposerJsonFiles();
    }
}
