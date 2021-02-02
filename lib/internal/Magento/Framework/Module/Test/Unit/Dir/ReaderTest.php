<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Framework\Module\Dir\File
 */
namespace Magento\Framework\Module\Test\Unit\Dir;

use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Module\Dir;

class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_moduleListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_protFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_dirsMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_baseConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_fileIteratorFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $directoryReadFactoryMock;

    protected function setUp(): void
    {
        $this->_protFactoryMock = $this->createMock(\Magento\Framework\App\Config\BaseFactory::class);
        $this->_dirsMock = $this->createMock(\Magento\Framework\Module\Dir::class);
        $this->_baseConfigMock = $this->createMock(\Magento\Framework\App\Config\Base::class);
        $this->_moduleListMock = $this->createMock(\Magento\Framework\Module\ModuleListInterface::class);
        $this->directoryReadFactoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadFactory::class);
        $this->_fileIteratorFactory = $this->createMock(\Magento\Framework\Config\FileIteratorFactory::class);

        $this->_model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            $this->_fileIteratorFactory,
            $this->directoryReadFactoryMock
        );
    }

    public function testGetModuleDirWhenCustomDirIsNotSet()
    {
        $this->_dirsMock->expects(
            $this->any()
        )->method(
            'getDir'
        )->with(
            'Test_Module',
            'etc'
        )->willReturn(
            'app/code/Test/Module/etc'
        );
        $this->assertEquals(
            'app/code/Test/Module/etc',
            $this->_model->getModuleDir(Dir::MODULE_ETC_DIR, 'Test_Module')
        );
    }

    public function testGetModuleDirWhenCustomDirIsSet()
    {
        $moduleDir = 'app/code/Test/Module/etc/custom';
        $this->_dirsMock->expects($this->never())->method('getDir');
        $this->_model->setModuleDir('Test_Module', 'etc', $moduleDir);
        $this->assertEquals($moduleDir, $this->_model->getModuleDir(Dir::MODULE_ETC_DIR, 'Test_Module'));
    }

    public function testGetConfigurationFiles()
    {
        $configPath = 'app/code/Test/Module/etc/config.xml';
        $modulesDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->willReturnArgument(0);
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->willReturn(true);
        $this->directoryReadFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($modulesDirectoryMock);

        $this->_moduleListMock->expects($this->once())->method('getNames')->willReturn(['Test_Module']);
        $model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            new FileIteratorFactory(
                new \Magento\Framework\Filesystem\File\ReadFactory(new \Magento\Framework\Filesystem\DriverPool())
            ),
            $this->directoryReadFactoryMock
        );
        $model->setModuleDir('Test_Module', 'etc', 'app/code/Test/Module/etc');

        $this->assertEquals($configPath, $model->getConfigurationFiles('config.xml')->key());
    }

    public function testGetComposerJsonFiles()
    {
        $configPath = 'app/code/Test/Module/composer.json';
        $modulesDirectoryMock = $this->createMock(\Magento\Framework\Filesystem\Directory\ReadInterface::class);
        $modulesDirectoryMock->expects($this->any())->method('getRelativePath')->willReturnArgument(0);
        $modulesDirectoryMock->expects($this->any())->method('isExist')
            ->with($configPath)
            ->willReturn(true);
        $this->directoryReadFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($modulesDirectoryMock);

        $this->_moduleListMock->expects($this->once())->method('getNames')->willReturn(['Test_Module']);
        $model = new \Magento\Framework\Module\Dir\Reader(
            $this->_dirsMock,
            $this->_moduleListMock,
            new FileIteratorFactory(
                new \Magento\Framework\Filesystem\File\ReadFactory(new \Magento\Framework\Filesystem\DriverPool())
            ),
            $this->directoryReadFactoryMock
        );
        $model->setModuleDir('Test_Module', '', 'app/code/Test/Module');

        $this->assertEquals($configPath, $model->getComposerJsonFiles()->key());
    }
}
