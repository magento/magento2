<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Quote\Item;

use Magento\Bundle\Model\Option as BundleOption;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\Quote\Item\Option;
use Magento\Bundle\Model\ResourceModel\Option\Collection as OptionsCollection;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionsCollection;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test bundle product options model
 */
class OptionTest extends TestCase
{
    /**
     * @var Option
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $priceCurrency = $this->createMock(PriceCurrencyInterface::class);

        $priceCurrency->method('convert')
            ->willReturnArgument(0);

        $this->model = new Option(
            new Json(),
            $priceCurrency
        );
    }

    /**
     * @param array $customOptions
     * @param array $expected
     * @dataProvider getSelectionOptionsDataProvider
     */
    public function testGetSelectionOptions(array $customOptions, array $expected): void
    {
        $bundleProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTypeInstance', 'getPriceModel', 'getStore'])
            ->getMock();

        $typeInstance = $this->createMock(Type::class);
        $typeInstance->method('getOptionsByIds')
            ->willReturnCallback([$this, 'getOptionsCollectionMock']);
        $typeInstance->method('getSelectionsByIds')
            ->willReturnCallback([$this, 'getSelectionsCollectionMock']);

        $priceModel = $this->createMock(Price::class);
        $priceModel->method('getSelectionFinalTotalPrice')
            ->willReturnCallback(
                function ($product, $selection) {
                    return $selection->getSelectionId() * 10 + $product->getId();
                }
            );

        $bundleProduct->method('getTypeInstance')
            ->willReturn($typeInstance);
        $bundleProduct->method('getPriceModel')
            ->willReturn($priceModel);

        $bundleProduct->setCustomOptions($this->getCustomOptions($customOptions));
        $this->assertEquals($expected, $this->model->getSelectionOptions($bundleProduct));
    }

    /**
     * @return array
     */
    public static function getSelectionOptionsDataProvider(): array
    {
        return [
            [
                [],
                []
            ],
            [
                [
                    'bundle_option_ids' => '[1,2]',
                ],
                []
            ],
            [
                [
                    'bundle_selection_ids' => '[11,21]',
                ],
                []
            ],
            [
                [
                    'bundle_option_ids' => '[1,2]',
                    'bundle_selection_ids' => '[11,21]',
                    'selection_qty_11' => '2',
                    'selection_qty_21' => '3',
                ],
                [
                    11 => [
                        [
                            'code' => 'bundle_selection_attributes',
                            'value' => '{"price":110,"qty":2,"option_label":"Option 1","option_id":1}'
                        ]
                    ],
                    21 => [
                        [
                            'code' => 'bundle_selection_attributes',
                            'value' => '{"price":210,"qty":3,"option_label":"Option 2","option_id":2}'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @param array $configuration
     * @return OptionInterface[]
     */
    private function getCustomOptions(array $configuration): array
    {
        $customOptions = [];
        foreach ($configuration as $code => $value) {
            $customOptions[$code] = $this->createConfiguredMock(
                OptionInterface::class,
                [
                    'getValue' => $value
                ]
            );
        }

        return $customOptions;
    }

    /**
     * @param array $ids
     * @return OptionsCollection
     * @throws \ReflectionException
     */
    public function getOptionsCollectionMock(array $ids): OptionsCollection
    {
        $optionsCollection = $this->getMockBuilder(OptionsCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $options = [];
        foreach ($ids as $id) {
            $option = $this->getMockBuilder(BundleOption::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getId', 'getTitle'])
                ->getMock();

            $option->method('getId')
                ->willReturn($id);

            $option->method('getTitle')
                ->willReturn('Option ' . $id);

            $options[$id] = $option;
        }
        $reflectionProperty = new \ReflectionProperty($optionsCollection, '_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($optionsCollection, $options);

        return $optionsCollection;
    }

    /**
     * @param array $ids
     * @return SelectionsCollection
     * @throws \ReflectionException
     */
    public function getSelectionsCollectionMock(array $ids): SelectionsCollection
    {
        $selectionsCollection = $this->getMockBuilder(SelectionsCollection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $selections = [];
        foreach ($ids as $id) {
            $selection = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->addMethods(['getSelectionId', 'getOptionId'])
                ->getMock();

            $selection->method('getSelectionId')
                ->willReturn($id);

            $selection->method('getOptionId')
                ->willReturn(intdiv($id, 10));

            $selections[$id] = $selection;
        }
        $reflectionProperty = new \ReflectionProperty($selectionsCollection, '_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($selectionsCollection, $selections);

        return $selectionsCollection;
    }
}
