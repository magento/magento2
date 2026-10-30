<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\Option as QuoteItemOption;
use Magento\QuoteGraphQl\Model\CartItem\DataProvider\CustomizableOption;
use Magento\QuoteGraphQl\Model\Resolver\CustomizableOptions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for CustomizableOptions resolver
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class CustomizableOptionsTest extends TestCase
{
    /**
     * @var CustomizableOptions
     */
    private $resolver;

    /**
     * @var CustomizableOption|MockObject
     */
    private $customizableOptionMock;

    /**
     * @var Field|MockObject
     */
    private $fieldMock;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var QuoteItem|MockObject
     */
    private $quoteItemMock;

    /**
     * @var QuoteItemOption|MockObject
     */
    private $quoteItemOptionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customizableOptionMock = $this->createMock(CustomizableOption::class);
        $this->fieldMock = $this->createMock(Field::class);
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->quoteItemMock = $this->createMock(QuoteItem::class);
        $this->quoteItemOptionMock = $this->createMock(QuoteItemOption::class);

        $this->resolver = new CustomizableOptions($this->customizableOptionMock);
    }

    /**
     * Test resolve method throws exception when model is not provided
     */
    public function testResolveThrowsExceptionWhenModelNotProvided(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('"model" value should be specified');

        $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, []);
    }

    /**
     * Test resolve method returns empty array when no option_ids found
     */
    public function testResolveReturnsEmptyArrayWhenNoOptionIds(): void
    {
        $value = ['model' => $this->quoteItemMock];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn(null);

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([], $result);
    }

    /**
     * Test resolve method returns empty array when option_ids value is null
     */
    public function testResolveReturnsEmptyArrayWhenOptionIdsValueIsNull(): void
    {
        $value = ['model' => $this->quoteItemMock];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(1))
            ->method('getValue')
            ->willReturn(null);

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([], $result);
    }

    /**
     * Test resolve method returns customizable options data when valid options exist
     */
    public function testResolveReturnsCustomizableOptionsData(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '1,2,3';
        $expectedOptionData1 = [
            'id' => 1,
            'label' => 'Option 1',
            'type' => 'field',
            'values' => []
        ];
        $expectedOptionData2 = [
            'id' => 2,
            'label' => 'Option 2',
            'type' => 'dropdown',
            'values' => []
        ];
        $expectedOptionData3 = [
            'id' => 3,
            'label' => 'Option 3',
            'type' => 'area',
            'values' => []
        ];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnCallback(
                function (
                    QuoteItem $item,
                    int $optionId
                ) use (
                    $expectedOptionData1,
                    $expectedOptionData2,
                    $expectedOptionData3
                ) {
                    switch ($optionId) {
                        case 1:
                            return $expectedOptionData1;
                        case 2:
                            return $expectedOptionData2;
                        case 3:
                            return $expectedOptionData3;
                        default:
                            return [];
                    }
                }
            );

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([$expectedOptionData1, $expectedOptionData2, $expectedOptionData3], $result);
    }

    /**
     * Test resolve method skips empty customizable options and returns only valid ones
     */
    public function testResolveSkipsEmptyCustomizableOptions(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '1,2,3';
        $expectedOptionData1 = [
            'id' => 1,
            'label' => 'Option 1',
            'type' => 'field',
            'values' => []
        ];
        $expectedOptionData3 = [
            'id' => 3,
            'label' => 'Option 3',
            'type' => 'area',
            'values' => []
        ];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnCallback(
                function (
                    QuoteItem $item,
                    int $optionId
                ) use (
                    $expectedOptionData1,
                    $expectedOptionData3
                ) {
                    switch ($optionId) {
                        case 1:
                            return $expectedOptionData1;
                        case 2:
                            return [];
                        case 3:
                            return $expectedOptionData3;
                        default:
                            return [];
                    }
                }
            );

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([$expectedOptionData1, $expectedOptionData3], $result);
        $this->assertCount(2, $result);
    }

    /**
     * Test resolve method handles all empty customizable options
     */
    public function testResolveHandlesAllEmptyCustomizableOptions(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '1,2,3';

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturn([]);

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([], $result);
        $this->assertCount(0, $result);
    }

    /**
     * Test resolve method handles mixed null and empty array returns
     */
    public function testResolveHandlesMixedNullAndEmptyArrayReturns(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '1,2,3,4';
        $expectedOptionData1 = [
            'id' => 1,
            'label' => 'Valid Option',
            'type' => 'field',
            'values' => []
        ];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->exactly(4))
            ->method('getData')
            ->willReturnCallback(
                function (
                    QuoteItem $item,
                    int $optionId
                ) use ($expectedOptionData1) {
                    switch ($optionId) {
                        case 1:
                            return $expectedOptionData1;
                        case 2:
                        case 3:
                        case 4:
                            return [];
                        default:
                            return [];
                    }
                }
            );

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([$expectedOptionData1], $result);
        $this->assertCount(1, $result);
    }

    /**
     * Test resolve method handles single option ID
     */
    public function testResolveHandlesSingleOptionId(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '1';
        $expectedOptionData = [
            'id' => 1,
            'label' => 'Single Option',
            'type' => 'field',
            'values' => []
        ];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->once())
            ->method('getData')
            ->with($this->quoteItemMock, 1)
            ->willReturn($expectedOptionData);

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([$expectedOptionData], $result);
        $this->assertCount(1, $result);
    }

    /**
     * Test resolve method handles empty option IDs string
     */
    public function testResolveHandlesEmptyOptionIdsString(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '';

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->once())
            ->method('getData')
            ->with($this->quoteItemMock, 0)
            ->willReturn([]);

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([], $result);
    }

    /**
     * Test resolve method handles whitespace-only option IDs
     */
    public function testResolveHandlesWhitespaceOnlyOptionIds(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '  ,  ,  ';

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturn([]);

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([], $result);
    }

    /**
     * Test resolve method handles deleted custom options by skipping them and returning only valid options
     */
    public function testResolveHandlesDeletedCustomOption(): void
    {
        $value = ['model' => $this->quoteItemMock];
        $optionIds = '1,999,2';
        $validOption1 = [
            'id' => 1,
            'label' => 'Valid Option 1',
            'type' => 'field',
            'values' => [
                [
                    'label' => 'Valid Value',
                    'value' => 'test'
                ]
            ]
        ];
        $validOption2 = [
            'id' => 2,
            'label' => 'Valid Option 2',
            'type' => 'dropdown',
            'values' => [
                [
                    'label' => 'Another Valid Value',
                    'value' => 'test2'
                ]
            ]
        ];

        $this->quoteItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('option_ids')
            ->willReturn($this->quoteItemOptionMock);

        $this->quoteItemOptionMock->expects($this->exactly(2))
            ->method('getValue')
            ->willReturn($optionIds);

        $this->customizableOptionMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnCallback(
                function (
                    QuoteItem $item,
                    int $optionId
                ) use (
                    $validOption1,
                    $validOption2
                ) {
                    switch ($optionId) {
                        case 1:
                            return $validOption1;
                        case 999:
                            return [];
                        case 2:
                            return $validOption2;
                        default:
                            return [];
                    }
                }
            );

        $result = $this->resolver->resolve($this->fieldMock, null, $this->resolveInfoMock, $value);

        $this->assertEquals([$validOption1, $validOption2], $result);
        $this->assertCount(2, $result);

        foreach ($result as $option) {
            $this->assertIsArray($option);
            $this->assertArrayHasKey('label', $option);
            $this->assertNotNull($option['label']);
        }
    }
}
