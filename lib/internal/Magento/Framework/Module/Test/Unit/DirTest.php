<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;

class DirTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $moduleRegistryMock;

    protected function setUp(): void
    {
        $this->moduleRegistryMock = $this->createMock(\Magento\Framework\Component\ComponentRegistrarInterface::class);

        $this->_model = new \Magento\Framework\Module\Dir($this->moduleRegistryMock);
    }

    public function testGetDirModuleRoot()
    {
        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->willReturn('/Test/Module');

        $this->assertEquals('/Test/Module', $this->_model->getDir('Test_Module'));
    }

    public function testGetDirModuleSubDir()
    {
        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->willReturn('/Test/Module');

        $this->assertEquals('/Test/Module/etc', $this->_model->getDir('Test_Module', 'etc'));
    }

    public function testGetSetupDirModule()
    {
        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->willReturn('/Test/Module');

        $this->assertEquals('/Test/Module/Setup', $this->_model->getDir('Test_Module', 'Setup'));
    }

    /**
     */
    public function testGetDirModuleSubDirUnknown()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory type \'unknown\' is not recognized');

        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->willReturn('/Test/Module');

        $this->_model->getDir('Test_Module', 'unknown');
    }

    /**
     */
    public function testGetDirModuleIncorrectlyRegistered()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Module \'Test Module\' is not correctly registered.');

        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with($this->identicalTo(ComponentRegistrar::MODULE), $this->identicalTo('Test Module'))
            ->willReturn(null);
        $this->_model->getDir('Test Module');
    }
}
