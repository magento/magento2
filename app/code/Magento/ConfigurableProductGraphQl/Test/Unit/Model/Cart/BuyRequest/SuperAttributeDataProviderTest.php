<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Test\Unit\Model\Cart\BuyRequest;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProductGraphQl\Model\Cart\BuyRequest\SuperAttributeDataProvider;
use Magento\ConfigurableProductGraphQl\Model\Options\Collection as OptionCollection;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for SuperAttributeDataProvider
 */
class SuperAttributeDataProviderTest extends TestCase
{
    /**
     * @var ArrayManager|MockObject
     */
    private $arrayManager;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var OptionCollection|MockObject
     */
    private $optionCollection;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPool;

    /**
     * @var StockStateInterface|MockObject
     */
    private $stockState;

    /**
     * @var SuperAttributeDataProvider
     */
    private $superAttributeDataProvider;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->arrayManager = $this->createMock(ArrayManager::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->optionCollection = $this->createMock(OptionCollection::class);
        $this->metadataPool = $this->createMock(MetadataPool::class);
        $this->stockState = $this->createMock(StockStateInterface::class);

        $this->superAttributeDataProvider = new SuperAttributeDataProvider(
            $this->arrayManager,
            $this->productRepository,
            $this->optionCollection,
            $this->metadataPool,
            $this->stockState
        );
    }

    /**
     * Check that website id is correctly retrieved
     */
    public function testExecute(): void
    {
        $quoteMock = $this->createMock(Quote::class);
        $cartItemData = [
            'data' => [
                'quantity' => 2.0,
                'sku' => 'simple1',
            ],
            'parent_sku' => 'configurable',
            'model' => $quoteMock,
        ];

        $this->arrayManager->method('get')
            ->withConsecutive(
                ['parent_sku', $cartItemData],
                ['data/sku', $cartItemData],
                ['data/quantity', $cartItemData],
                ['model', $cartItemData],
            )
            ->willReturnOnConsecutiveCalls(
                'configurable',
                'simple1',
                2.0,
                $quoteMock,
            );

        $websiteId = 1;
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->atLeastOnce())
            ->method('getWebsiteId')
            ->willReturn($websiteId);
        $storeMock->expects($this->never())->method('getWebsite');
        $quoteMock->expects($this->atLeastOnce())
            ->method('getStore')
            ->willReturn($storeMock);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getExtensionAttributes', 'getData', 'getWebsiteIds'])
            ->addMethods(['getConfigurableProductLinks'])
            ->getMock();
        $productMock->method('getId')
            ->willReturn(1);
        $productMock->method('getExtensionAttributes')
            ->willReturnSelf();
        $productMock->method('getConfigurableProductLinks')
            ->willReturn([1]);
        $productMock->method('getData')
            ->willReturn(1);
        $productMock->method('getWebsiteIds')
            ->willReturn([$websiteId]);
        $this->productRepository->method('get')
            ->willReturn($productMock);
        $checkResult = new \Magento\Framework\DataObject();
        $checkResult->setHasError(false);
        $this->stockState->method('checkQuoteItemQty')
            ->willReturn($checkResult);
        $productMetadata = $this->createMock(EntityMetadataInterface::class);
        $productMetadata->method('getLinkField')
            ->willReturn('entity_id');
        $this->metadataPool->method('getMetadata')
            ->with(ProductInterface::class)
            ->willReturn($productMetadata);
        $this->optionCollection->method('getAttributesByProductId')
            ->willReturn([
                [
                    'attribute_code' => 'code',
                    'attribute_id' => 1,
                    'values' => [['value_index' => 1]],
                ]
            ]);

        $this->assertEquals(['super_attribute' => [1 => 1]], $this->superAttributeDataProvider->execute($cartItemData));
    }
}
