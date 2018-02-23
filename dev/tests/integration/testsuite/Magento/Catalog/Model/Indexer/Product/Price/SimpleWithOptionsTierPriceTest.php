<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Indexer\Product\Price;

class SimpleWithOptionsTierPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice
     */
    private $priceResource;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->productCollectionFactory = $this->objectManager->create(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class
        );
        $this->priceResource = $this->objectManager->get(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_with_options.php
     */
    public function testTierPrice()
    {
        $tierPriceValue = 9.00;

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        /** @var \Magento\Catalog\Api\ScopedProductTierPriceManagementInterface $tierPriceManagement */
        $tierPriceManagement = $this->objectManager->create(
            \Magento\Catalog\Api\ScopedProductTierPriceManagementInterface::class
        );

        /** @var \Magento\Catalog\Api\Data\ProductTierPriceInterface $tierPrice */
        $tierPrice = $this->objectManager->create(\Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory::class)
            ->create();

        $tierPrice->setCustomerGroupId(\Magento\Customer\Model\Group::CUST_GROUP_ALL);
        $tierPrice->setQty(1.00);
        $tierPrice->setValue($tierPriceValue);

        $tierPriceManagement->add($product->getSku(), $tierPrice);

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addIdFilter([$product->getId()]);
        $productCollection->addPriceData();
        $productCollection->load();
        $indexPriceInfo = $productCollection->getFirstItem();

        $tierPriceModel = $indexPriceInfo->getPriceInfo()
            ->getPrice(\Magento\Catalog\Pricing\Price\TierPrice::PRICE_CODE);

        $this->assertEquals($tierPriceValue, $tierPriceModel->getValue());

        $connection = $this->priceResource->getConnection();
        $select = $connection
            ->select()
            ->from($this->priceResource->getTable('catalog_product_index_tier_price'), 'min_price')
            ->where('entity_id = ?', $product->getId())
            ->where('customer_group_id = ?', 0)
            ->where('website_id IN (?)', $product->getWebsiteIds());
        $result = $connection->fetchRow($select);

        $this->assertEquals($tierPriceValue, $result['min_price']);
    }
}
