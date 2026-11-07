<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Option as OptionResource;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store as StoreFixture;
use Magento\TestFramework\Fixture\AppIsolation;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureBeforeTransaction;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

#[AppIsolation(true)]
#[DbIsolation(true)]
class CollectionOptionsTest extends TestCase
{
    #[DataFixtureBeforeTransaction(StoreFixture::class, as: 'store')]
    #[DataFixture(
        ProductFixture::class,
        [
            'options' => [
                [
                    'type' => 'drop_down',
                    'title' => 'Default option',
                    'values' => [['title' => 'Default value', 'price' => 0]],
                ],
            ],
        ],
        as: 'product'
    )]
    public function testAddOptionsToResultUsesCollectionStore(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $fixtures = DataFixtureStorageManager::getStorage();
        $storeId = (int)$fixtures->get('store')->getId();
        $product = $objectManager->get(ProductRepositoryInterface::class)->get(
            $fixtures->get('product')->getSku(),
            false,
            $storeId,
            true
        );
        $option = array_values($product->getOptions())[0];
        $option->setStoreId($storeId);
        $option->setTitle('Store option');
        $option->setIsUseDefault(false);
        $value = array_values($option->getValues())[0];
        $value->setTitle('Store value');
        $value->setIsUseDefault(false);
        $objectManager->get(OptionResource::class)->save($option);

        $storeManager = $objectManager->get(StoreManagerInterface::class);
        $storeManager->setCurrentStore($storeManager->getDefaultStoreView());
        self::assertNotEquals($storeId, $storeManager->getStore()->getId());
        $collection = $objectManager->create(Collection::class);
        $collection->setStoreId($storeId)->addIdFilter($product->getId())->addOptionsToResult();
        $loadedOption = array_values($collection->getFirstItem()->getOptions())[0];
        self::assertSame('Store option', $loadedOption->getTitle());
        self::assertSame('Store value', array_values($loadedOption->getValues())[0]->getTitle());
    }
}
