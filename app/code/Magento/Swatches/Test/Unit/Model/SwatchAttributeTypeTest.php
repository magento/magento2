<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributeType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for \Magento\Swatches\Model\SwatchAttributeType class.
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class SwatchAttributeTypeTest extends TestCase
{
    use MockCreationTrait;
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
     * @param string $dataValue
     * @param bool $expected
     * @return void
     */
    #[DataProvider('provideIsSwatchAttributeTestData')]
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
    public static function provideIsSwatchAttributeTestData() : array
    {
        return [
            [Swatch::SWATCH_INPUT_TYPE_TEXT, true],
            [Swatch::SWATCH_INPUT_TYPE_VISUAL, true],
            ['fake', false],
        ];
    }

    /**
     * @param string $dataValue
     * @param bool $expected
     * @return void
     */
    #[DataProvider('provideIsTextSwatchAttributeTestData')]
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
    public static function provideIsTextSwatchAttributeTestData() : array
    {
        return [
            [Swatch::SWATCH_INPUT_TYPE_TEXT, true],
            [Swatch::SWATCH_INPUT_TYPE_VISUAL, false],
            ['fake', false],
        ];
    }

    /**
     * @param string $dataValue
     * @param bool $expected
     * @return void
     */
    #[DataProvider('provideIsVisualSwatchAttributeTestData')]
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
    public static function provideIsVisualSwatchAttributeTestData() : array
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

        $attributeMock = $this->createPartialMockWithReflection(Attribute::class, ['hasData', 'getData', 'setData']);
        $data = [];
        $attributeMock->method('hasData')->willReturnCallback(function ($key) use (&$data) {
            return isset($data[$key]);
        });
        $attributeMock->method('getData')->willReturnCallback(function ($key = '') use (&$data, $encodedAdditionData) {
            if ($key === 'additional_data') {
                return $encodedAdditionData;
            }
            return $data[$key] ?? null;
        });
        $attributeMock->method('setData')->willReturnCallback(function ($key, $value = null) use (&$data) {
            $data[$key] = $value;
        });

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
        $attributeMock = $this->createPartialMock(
            Attribute::class,
            ['hasData', 'getData']
        );
        $attributeMock->method('hasData')->willReturn($hasDataReturns);
        $attributeMock->method('getData')->willReturn($getDataReturns);
        return $attributeMock;
    }
}
