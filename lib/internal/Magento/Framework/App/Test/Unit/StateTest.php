<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\App\Test\Unit;

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

    public function testSetAreaCode()
    {
        $areaCode = 'some code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->setExpectedException('Magento\Framework\Exception\LocalizedException');
        $this->model->setAreaCode('any code');
    }

    public function testGetAreaCodeException()
    {
        $this->scopeMock->expects($this->never())->method('setCurrentScope');
        $this->setExpectedException('Magento\Framework\Exception\LocalizedException');
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

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function emulateAreaCodeCallback()
    {
        return $this->model->getAreaCode();
    }

    public function testIsAreaCodeEmulated()
    {
        $areaCode = 'original code';
        $emulatedCode = 'emulated code';
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->assertFalse(
            $this->model->isAreaCodeEmulated(),
            'By default, area code is not emulated'
        );
        $this->assertTrue(
            $this->model->emulateAreaCode($emulatedCode, [$this, 'isAreaCodeEmulatedCallback']),
            'isAreaCodeEmulated should return true when being called within the context of an emulated method'
        );
        $this->assertFalse(
            $this->model->isAreaCodeEmulated(),
            'Now that emulateAreaCode execution has finished, this should return false again'
        );
    }

    /**
     * Used to test whether the isAreaCodeEmulated method returns true within an emulated context
     *
     * @return bool
     */
    public function isAreaCodeEmulatedCallback()
    {
        return $this->model->isAreaCodeEmulated();
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

    /**
     * @param string $mode
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($mode)
    {
        $model = new \Magento\Framework\App\State(
            $this->getMockForAbstractClass('Magento\Framework\Config\ScopeInterface', [], '', false),
            $mode
        );
        $this->assertEquals($mode, $model->getMode());
    }

    /**
     * @return array
     */
    public static function constructorDataProvider()
    {
        return [
            'default mode' => [\Magento\Framework\App\State::MODE_DEFAULT],
            'production mode' => [\Magento\Framework\App\State::MODE_PRODUCTION],
            'developer mode' => [\Magento\Framework\App\State::MODE_DEVELOPER]
        ];
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unknown application mode: unknown mode
     */
    public function testConstructorException()
    {
        new \Magento\Framework\App\State(
            $this->getMockForAbstractClass('Magento\Framework\Config\ScopeInterface', [], '', false),
            "unknown mode"
        );
    }
}
