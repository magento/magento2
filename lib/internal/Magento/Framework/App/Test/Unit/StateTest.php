<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Area;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateTest extends TestCase
{
    /**
     * @var State
     */
    protected $model;

    /**
     * @var ScopeInterface|MockObject
     */
    protected $scopeMock;

    /**
     * @var AreaList|MockObject
     */
    protected $areaListMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManagerHelper($this);
        $this->scopeMock = $this->getMockForAbstractClass(
            ScopeInterface::class,
            ['setCurrentScope'],
            '',
            false
        );

        $this->areaListMock = $this->createMock(AreaList::class);
        $this->areaListMock->expects($this->any())
            ->method('getCodes')
            ->willReturn([Area::AREA_ADMINHTML, Area::AREA_FRONTEND]);

        $this->model = $objectManager->getObject(
            State::class,
            ['configScope' => $this->scopeMock]
        );

        $objectManager->setBackwardCompatibleProperty($this->model, 'areaList', $this->areaListMock);
    }

    public function testSetAreaCode()
    {
        $areaCode = Area::AREA_FRONTEND;
        $this->scopeMock->expects($this->once())->method('setCurrentScope')->with($areaCode);
        $this->model->setAreaCode($areaCode);
        $this->expectException(LocalizedException::class);
        $this->model->setAreaCode(Area::AREA_ADMINHTML);
    }

    public function testGetAreaCodeException()
    {
        $this->scopeMock->expects($this->never())->method('setCurrentScope');
        $this->expectException(LocalizedException::class);
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
     * @throws LocalizedException
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

    public function testEmulateAreaCodeException()
    {
        $this->expectException('Exception');
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
        $model = new State(
            $this->getMockForAbstractClass(ScopeInterface::class, [], '', false),
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
            'default mode' => [State::MODE_DEFAULT],
            'production mode' => [State::MODE_PRODUCTION],
            'developer mode' => [State::MODE_DEVELOPER]
        ];
    }

    public function testConstructorException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Unknown application mode: unknown mode');
        new State(
            $this->getMockForAbstractClass(ScopeInterface::class, [], '', false),
            "unknown mode"
        );
    }

    public function testCheckAreaCodeException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->expectExceptionMessage('Area code "any code" does not exist');
        $this->model->setAreaCode('any code');
    }
}
