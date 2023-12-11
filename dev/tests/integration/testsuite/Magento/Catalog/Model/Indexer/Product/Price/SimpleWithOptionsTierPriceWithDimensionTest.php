<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Indexer\Product\Price;

use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Fixture\IndexerDimensionMode;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ScopedProductTierPriceManagementInterface;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Pricing\Price\TierPrice;
use Magento\Customer\Model\Group as CustomerGroup;

/**
 * @group indexer_dimension
 */
class SimpleWithOptionsTierPriceWithDimensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * set up
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->objectManager->create(CollectionFactory::class);
    }

    #[
        DbIsolation(false),
        IndexerDimensionMode('catalog_product_price', 'website_and_customer_group'),
        DataFixture(CategoryFixture::class, as: 'category'),
        DataFixture(ProductFixture::class, ['category_ids' => ['$category.id$']], 'product'),
    ]
    public function testTierPrice()
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get('product');
        $tierPriceValue = 9.00;

        $tierPrice = $this->objectManager->create(ProductTierPriceInterfaceFactory::class)
            ->create();
        $tierPrice->setCustomerGroupId(CustomerGroup::CUST_GROUP_ALL);
        $tierPrice->setQty(1.00);
        $tierPrice->setValue($tierPriceValue);
        $tierPriceManagement = $this->objectManager->create(ScopedProductTierPriceManagementInterface::class);
        $tierPriceManagement->add($product->getSku(), $tierPrice);

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter($product->getId());
        $productCollection->addPriceData();
        $productCollection->load();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $productCollection->getFirstItem();
        $tierPrice = $product->getPriceInfo()
            ->getPrice(TierPrice::PRICE_CODE)
            ->getValue();

        $this->assertEquals($tierPriceValue, $tierPrice);

        $tierPrice = $product->getTierPrice(1);
        $this->assertEquals($tierPriceValue, $tierPrice);

        $tierPrices = $product->getData('tier_price');
        $this->assertEquals($tierPriceValue, $tierPrices[0]['price']);

        $minPrice = $product->getData('min_price');
        $this->assertEquals($tierPriceValue, $minPrice);
    }
}
