<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributeType;

class SwatchAttributeTypeTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var SwatchAttributeType
     */
    private $swatchType;

    protected function setUp()
    {
        parent::setUp();
        $this->swatchType = new SwatchAttributeType(new Json());
    }

    /**
     * @dataProvider provideIsSwatchAttributeTestData
     * @param string $dataValue
     * @param bool $expected
     */
    public function testIsSwatchAttribute($dataValue, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->swatchType->isSwatchAttribute(
                $this->createAttributeMock($dataValue)
            )
        );
    }

    /**
     * DataProvider for testIsSwatchAttribute
     * @return array
     */
    public function provideIsSwatchAttributeTestData()
    {
        return [
            [Swatch::SWATCH_INPUT_TYPE_TEXT, true],
            [Swatch::SWATCH_INPUT_TYPE_VISUAL, true],
            ['fake', false],
        ];
    }

    /**
     * @dataProvider provideIsTextSwatchAttributeTestData
     * @param string $dataValue
     * @param bool $expected
     */
    public function testIsTextSwatch($dataValue, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->swatchType->isTextSwatch(
                $this->createAttributeMock($dataValue)
            )
        );
    }

    /**
     * DataProvider for testIsTextSwatch
     * @return array
     */
    public function provideIsTextSwatchAttributeTestData()
    {
        return [
            [Swatch::SWATCH_INPUT_TYPE_TEXT, true],
            [Swatch::SWATCH_INPUT_TYPE_VISUAL, false],
            ['fake', false],
        ];
    }

    /**
     * @dataProvider provideIsVisualSwatchAttributeTestData
     * @param string $dataValue
     * @param bool $expected
     */
    public function testIsVisualSwatch($dataValue, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->swatchType->isVisualSwatch(
                $this->createAttributeMock($dataValue)
            )
        );
    }

    /**
     * DataProvider for testIsTextSwatch
     * @return array
     */
    public function provideIsVisualSwatchAttributeTestData()
    {
        return [
            [Swatch::SWATCH_INPUT_TYPE_VISUAL, true],
            [Swatch::SWATCH_INPUT_TYPE_TEXT, false],
            ['fake', false],
        ];
    }

    public function testIfAttributeHasNotAdditionData()
    {
        /** @var Json $json */
        $json = new Json();
        $encodedAdditionData = $json->serialize([Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_TEXT]);

        /** @var AttributeInterface | \PHPUnit_Framework_MockObject_MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData', 'getData', 'setData'])
            ->getMockForAbstractClass();

        $attributeMock->expects($this->any())->method('hasData')->willReturn(false);
        $attributeMock->expects($this->at(0))->method('getData')->willReturn('test');
        $attributeMock->expects($this->at(1))->method('getData')->willReturn($encodedAdditionData);
        $attributeMock->expects($this->at(2))->method('getData')->willReturn(Swatch::SWATCH_INPUT_TYPE_TEXT);
        $attributeMock->expects($this->at(3))->method('getData')->willReturn(Swatch::SWATCH_INPUT_TYPE_TEXT);

        $this->assertEquals(true, $this->swatchType->isTextSwatch($attributeMock));
        $this->assertEquals(false, $this->swatchType->isVisualSwatch($attributeMock));
    }

    /**
     * @param mixed $getDataReturns
     * @param bool $hasDataReturns
     * @return AttributeInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createAttributeMock($getDataReturns, $hasDataReturns = true)
    {
        $attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData', 'getData', 'setData'])
            ->getMockForAbstractClass();

        $attributeMock->expects($this->any())->method('hasData')->willReturn($hasDataReturns);
        $attributeMock->expects($this->any())->method('getData')->willReturn($getDataReturns);
        return $attributeMock;
    }
}
