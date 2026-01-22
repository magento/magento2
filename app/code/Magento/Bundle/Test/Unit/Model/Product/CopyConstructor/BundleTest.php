<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product\CopyConstructor;

use Magento\Bundle\Model\Link;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\CopyConstructor\Bundle;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Bundle\Model\Product\CopyConstructor\Bundle
 */
class BundleTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Bundle
     */
    protected $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(Bundle::class);
    }

    /**
     * @return void
     */
    public function testBuildNegative()
    {
        $product = $this->createMock(Product::class);
        $duplicate = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn('other product type');
        $this->model->build($product, $duplicate);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testBuildPositive()
    {
        /** @var Product|MockObject $product */
        $product = $this->createMock(Product::class);
        $extensionAttributesProduct = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getBundleProductOptions', 'setBundleProductOptions']
        );

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesProduct);
        $bundleOptionsData = [
            [
                'option_id' => 1,
                'title' => 'Option 1',
                'product_links' => [
                    [
                        'option_id' => 1,
                        'id' => 1,
                        'selection_id' => 1,
                        'sku' => 'sku-1'
                    ],
                ]
            ],
            [
                'option_id' => 2,
                'title' => 'Option 2',
                'product_links' => [
                    [
                        'option_id' => 2,
                        'id' => 2,
                        'selection_id' => 2,
                        'sku' => 'sku-2'
                    ]
                ]
            ]
        ];
        $bundleOptions = array_map(
            fn ($optionData) => $this->createOptionMock(
                [...$optionData, 'product_links' => array_map($this->createLinkMock(...), $optionData['product_links'])]
            ),
            $bundleOptionsData
        );

        // Configure mock to return bundleOptions
        $extensionAttributesProduct->method('getBundleProductOptions')->willReturn($bundleOptions);

        /** @var Product|MockObject $duplicate */
        $duplicate = $this->createMock(Product::class);
        $extensionAttributesDuplicate = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['setBundleProductOptions']
        );

        $bundleOptionsClone = null;
        $extensionAttributesDuplicate->expects($this->once())
            ->method('setBundleProductOptions')
            ->willReturnCallback(function ($options) use (&$bundleOptionsClone) {
                $bundleOptionsClone = $options;
            });

        $duplicate->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesDuplicate);
        
        $this->model->build($product, $duplicate);
        $this->assertNotEmpty($bundleOptionsClone, 'Bundle options should be set on duplicate product');

        foreach ($bundleOptionsData as $key => $optionData) {
            $bundleOption = $bundleOptions[$key];
            $bundleOptionClone = $bundleOptionsClone[$key];

            $this->assertEquals($optionData['option_id'], $bundleOption->getOptionId());
            $this->assertEquals($optionData['title'], $bundleOption->getTitle());

            $this->assertNotEquals($bundleOption, $bundleOptionClone);

            $this->assertNull($bundleOptionClone->getOptionId());
            $this->assertEquals($optionData['title'], $bundleOptionClone->getTitle());

            foreach ($optionData['product_links'] as $productLinkKey => $productLinkData) {
                $productLink = $bundleOption->getProductLinks()[$productLinkKey];
                $productLinkClone = $bundleOptionClone->getProductLinks()[$productLinkKey];

                $this->assertEquals($productLinkData['option_id'], $productLink->getOptionId());
                $this->assertEquals($productLinkData['id'], $productLink->getId());
                $this->assertEquals($productLinkData['selection_id'], $productLink->getSelectionId());
                $this->assertEquals($productLinkData['sku'], $productLink->getSku());

                $this->assertNotEquals($productLink, $productLinkClone);

                $this->assertNull($productLinkClone->getId());
                $this->assertNull($productLinkClone->getOptionId());
                $this->assertNull($productLinkClone->getSelectionId());
                $this->assertEquals($productLinkData['sku'], $productLinkClone->getSku());
            }
        }
    }

    /**
     * @param array $data
     * @return Option
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function createOptionMock(array $data): Option
    {
        $option = $this->createPartialMock(Option::class, []);
        $option->addData($data);
        return $option;
    }

    /**
     * @param array $data
     * @return Link
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function createLinkMock(array $data): Link
    {
        $productLink = $this->createPartialMock(Link::class, []);
        $productLink->addData($data);
        return $productLink;
    }

    /**
     * @return void
     */
    public function testBuildWithoutOptions()
    {
        $product = $this->createMock(Product::class);
        $extensionAttributesProduct = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['getBundleProductOptions']
        );

        $product->expects($this->once())
            ->method('getTypeId')
            ->willReturn(Type::TYPE_BUNDLE);
        $product->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesProduct);

        $extensionAttributesProduct->method('getBundleProductOptions')->willReturn(null);

        $duplicate = $this->createMock(Product::class);
        $extensionAttributesDuplicate = $this->createPartialMockWithReflection(
            ProductExtensionInterface::class,
            ['setBundleProductOptions']
        );

        $extensionAttributesDuplicate->expects($this->once())
            ->method('setBundleProductOptions')
            ->with([]);

        $duplicate->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributesDuplicate);

        $this->model->build($product, $duplicate);
    }
}
