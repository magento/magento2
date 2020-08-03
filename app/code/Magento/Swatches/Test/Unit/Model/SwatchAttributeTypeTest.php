<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributeType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for \Magento\Swatches\Model\SwatchAttributeType class.
 */
class SwatchAttributeTypeTest extends TestCase
{
    /**
     * @var SwatchAttributeType
     */
    private $swatchType;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->swatchType = new SwatchAttributeType(new Json());
    }

    /**
     * @dataProvider provideIsSwatchAttributeTestData
     * @param string $dataValue
     * @param bool $expected
     * @return void
     */
    public function testIsSwatchAttribute(string $dataValue, bool $expected) : void
    {
        $this->assertEquals(
            $expected,
            $this->swatchType->isSwatchAttribute(
                $this->createAttributeMock($dataValue)
            )
        );
    }

    /**
     * DataProvider for testIsSwatchAttribute.
     *
     * @return array
     */
    public function provideIsSwatchAttributeTestData() : array
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
     * @return void
     */
    public function testIsTextSwatch(string $dataValue, bool $expected) : void
    {
        $this->assertEquals(
            $expected,
            $this->swatchType->isTextSwatch(
                $this->createAttributeMock($dataValue)
            )
        );
    }

    /**
     * DataProvider for testIsTextSwatch.
     *
     * @return array
     */
    public function provideIsTextSwatchAttributeTestData() : array
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
     * @return void
     */
    public function testIsVisualSwatch(string $dataValue, bool $expected) : void
    {
        $this->assertEquals(
            $expected,
            $this->swatchType->isVisualSwatch(
                $this->createAttributeMock($dataValue)
            )
        );
    }

    /**
     * DataProvider for testIsTextSwatch.
     *
     * @return array
     */
    public function provideIsVisualSwatchAttributeTestData() : array
    {
        return [
            [Swatch::SWATCH_INPUT_TYPE_VISUAL, true],
            [Swatch::SWATCH_INPUT_TYPE_TEXT, false],
            ['fake', false],
        ];
    }

    /**
     * @return void
     */
    public function testIfAttributeHasNotAdditionData() : void
    {
        /** @var Json $json */
        $json = new Json();
        $encodedAdditionData = $json->serialize([Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_TEXT]);

        /** @var AttributeInterface|MockObject $attributeMock */
        $attributeMock = $this->getMockBuilder(AttributeInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['hasData', 'getData', 'setData'])
            ->getMockForAbstractClass();

        $attributeMock->expects($this->any())->method('hasData')->willReturn(false);

        $attributeMock->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['additional_data', $encodedAdditionData],
                    [Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_TEXT],
                ]
            );

        $this->assertTrue($this->swatchType->isTextSwatch($attributeMock));
        $this->assertFalse($this->swatchType->isVisualSwatch($attributeMock));
    }

    /**
     * @param mixed $getDataReturns
     * @param bool $hasDataReturns
     * @return AttributeInterface|MockObject
     */
    protected function createAttributeMock($getDataReturns, bool $hasDataReturns = true)
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
