<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Test\Unit;

use Magento\Framework\Component\ComponentRegistrar;

class DirTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Dir
     */
    protected $_model;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleRegistryMock;

    protected function setUp()
    {
        $this->moduleRegistryMock = $this->getMock(
            'Magento\Framework\Component\ComponentRegistrarInterface',
            [],
            [],
            '',
            false,
            false
        );

        $this->_model = new \Magento\Framework\Module\Dir($this->moduleRegistryMock);
    }

    public function testGetDirModuleRoot()
    {
        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->will($this->returnValue('/Test/Module'));

        $this->assertEquals('/Test/Module', $this->_model->getDir('Test_Module'));
    }

    public function testGetDirModuleSubDir()
    {
        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->will($this->returnValue('/Test/Module'));

        $this->assertEquals('/Test/Module/etc', $this->_model->getDir('Test_Module', 'etc'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Directory type 'unknown' is not recognized
     */
    public function testGetDirModuleSubDirUnknown()
    {
        $this->moduleRegistryMock->expects($this->once())
            ->method('getPath')
            ->with(ComponentRegistrar::MODULE, 'Test_Module')
            ->will($this->returnValue('/Test/Module'));

        $this->_model->getDir('Test_Module', 'unknown');
    }
}
