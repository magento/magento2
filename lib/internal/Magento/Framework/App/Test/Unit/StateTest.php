<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit;

use \Magento\Framework\App\Area;
use \Magento\Framework\App\AreaList;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class StateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $model;

    /**
     * @var \Magento\Framework\Config\ScopeInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeMock;

    /**
     * @var AreaList|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $areaListMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->scopeMock = $this->getMockForAbstractClass(
            \Magento\Framework\Config\ScopeInterface::class,
            ['setCurrentScope'],
            '',
            false
        );

        $this->areaListMock = $this->createMock(AreaList::class);
        $this->areaListMock->expects($this->any())
            ->method('getCodes')
            ->willReturn([Area::AREA_ADMINHTML, Area::AREA_FRONTEND]);

        $this->model = $objectManager->getObject(
            \Magento\Framework\App\State::class,
            ['configScope' => $this->scopeMock]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'areaList', $this->areaListMock);
    }

    public function testSetAreaCode()
    {
        $areaCode = Area::AREA_FRONTEND;
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->model->setAreaCode(Area::AREA_ADMINHTML);
    }

    public function testGetAreaCodeException()
    {
        $this->scopeMock->expects($this->never())->method('setCurrentScope');
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->model->getAreaCode();
    }

    public function testGetAreaCode()
    {
        $areaCode = Area::AREA_FRONTEND;
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->assertEquals($areaCode, $this->model->getAreaCode());
    }

    public function testEmulateAreaCode()
    {
        $areaCode = Area::AREA_FRONTEND;
        $emulatedCode = Area::AREA_ADMINHTML;
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
        $areaCode = Area::AREA_ADMINHTML;
        $emulatedCode = Area::AREA_FRONTEND;
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
     */
    public function testEmulateAreaCodeException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Some error');

        $areaCode = Area::AREA_FRONTEND;
        $emulatedCode = Area::AREA_ADMINHTML;
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
            $this->getMockForAbstractClass(\Magento\Framework\Config\ScopeInterface::class, [], '', false),
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
     */
    public function testConstructorException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown application mode: unknown mode');

        new \Magento\Framework\App\State(
            $this->getMockForAbstractClass(\Magento\Framework\Config\ScopeInterface::class, [], '', false),
            "unknown mode"
        );
    }

    /**
     */
    public function testCheckAreaCodeException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Area code "any code" does not exist');

        $this->model->setAreaCode('any code');
    }
}
