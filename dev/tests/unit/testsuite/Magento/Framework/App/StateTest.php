<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\Config\ScopeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeMock;

    protected function setUp()
    {
        $this->scopeMock = $this->getMockForAbstractClass(
            'Magento\Framework\Config\ScopeInterface',
            ['setCurrentScope'],
            '',
            false
        );
        $this->model = new \Magento\Framework\App\State($this->scopeMock);
    }

    public function testSetGetUpdateMode()
    {
        $this->assertFalse($this->model->getUpdateMode());
        $this->model->setUpdateMode(true);
        $this->assertTrue($this->model->getUpdateMode());
    }

    public function testSetAreaCode()
    {
        $areaCode = 'some code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->setExpectedException('Magento\Framework\Exception');
        $this->model->setAreaCode('any code');
    }

    public function testGetAreaCodeException()
    {
        $this->scopeMock->expects($this->never())->method('setCurrentScope');
        $this->setExpectedException('Magento\Framework\Exception');
        $this->model->getAreaCode();
    }

    public function testGetAreaCode()
    {
        $areaCode = 'some code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->assertEquals($areaCode, $this->model->getAreaCode());
    }

    public function testEmulateAreaCode()
    {
        $areaCode = 'original code';
        $emulatedCode = 'emulated code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->assertEquals(
            $emulatedCode,
            $this->model->emulateAreaCode($emulatedCode, [$this, 'emulateAreaCodeCallback'])
        );
        $this->assertEquals($this->model->getAreaCode(), $areaCode);
    }

    public function emulateAreaCodeCallback()
    {
        return $this->model->getAreaCode();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Some error
     */
    public function testEmulateAreaCodeException()
    {
        $areaCode = 'original code';
        $emulatedCode = 'emulated code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->model->emulateAreaCode($emulatedCode, [$this, 'emulateAreaCodeCallbackException']);
        $this->assertEquals($this->model->getAreaCode(), $areaCode);
    }

    public function emulateAreaCodeCallbackException()
    {
        throw new \Exception('Some error');
    }
}
