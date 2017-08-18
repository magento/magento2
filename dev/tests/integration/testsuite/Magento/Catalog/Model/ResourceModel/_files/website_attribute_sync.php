<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use \Magento\Store\Model\Website;
use \Magento\Store\Model\ResourceModel\Website as WebsiteResourceModel;
use \Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group as GroupResourceModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\App\ResourceConnection;
use \Magento\Catalog\Model\Product\Attribute\Source\Status as AttributeStatus;
use \Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Attribute\WebsiteAttributesSynchronizer;
use \Magento\TestFramework\Helper\Bootstrap;

/**
 * create whole website->storeGroup->[store1,store2] structure and add product to it with
 * "Status" website attribute values that have different values in the same website (which is wrong behavior)
 * also turns on "Require sync" flag
 */

$productId = 333;
$objectManager = Bootstrap::getObjectManager();
$websiteResourceModel = $objectManager->get(WebsiteResourceModel::class);
$storeGroupResourceModel = $objectManager->get(GroupResourceModel::class);
$storeResourceModel = $objectManager->get(StoreResourceModel::class);
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$resourceConnection = $objectManager->get(ResourceConnection::class);
$flagManager = $objectManager->get(\Magento\Framework\FlagManager::class);
$productLinkField = $objectManager->get(\Magento\Framework\EntityManager\MetadataPool::class)
    ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
    ->getLinkField();

/**
 * Create Website
 */

/**
 * @var Website $website
 */
$website = $objectManager->create(Website::class);
$website->setName('custom website for av test')
    ->setCode('customwebsite1');
$website->isObjectNew(true);
$websiteResourceModel->save($website);


/**
 * Create store group
 */

/**
 * @var Group $storeGroup
 */
$storeGroup = $objectManager->create(Group::class);
$storeGroup->setCode('customstoregroup1')
    ->setName('custom store group for av test')
    ->setWebsite($website);

$storeGroupResourceModel->save($storeGroup);

$website->setDefaultGroupId($storeGroup->getId());
$websiteResourceModel->save($website);

/**
 * Create Stores
 */
/**
 * @var Store $storeOne
 */
$storeOne = $objectManager->create(Store::class);
$storeOne->setName('custom store for av test')
    ->setCode('customstoreview1')
    ->setGroup($storeGroup);
$storeOne->setWebsite($website);

$storeResourceModel->save($storeOne);

$storeGroup->setDefaultStoreId($storeOne->getId());
$storeGroupResourceModel->save($storeGroup);

/**
 * @var Store $storeTwo
 */
$storeTwo = $objectManager->create(Store::class);
$storeTwo->setName('custom store for av test 2')
    ->setCode('customstoreview2')
    ->setGroup($storeGroup);
$storeTwo->setWebsite($website);
$storeResourceModel->save($storeTwo);
$stores = [
    $storeOne,
    $storeTwo,
];

$objectManager
    ->create(\Magento\CatalogSearch\Model\Indexer\Fulltext\Processor::class)
    ->reindexAll();

/**
 * Create product
 */

/** @var $product Product */
$product = $objectManager->create(Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId($productId)
    ->setAttributeSetId(4)
    ->setName('Simple Product custom')
    ->setSku('simplecustomproduct')
    ->setTaxClassId('none')
    ->setDescription('description')
    ->setShortDescription('short description')
    ->setOptionsContainer('container1')
    ->setMsrpDisplayActualPriceType(\Magento\Msrp\Model\Product\Attribute\Source\Type::TYPE_IN_CART)
    ->setPrice(10)
    ->setWeight(1)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(AttributeStatus::STATUS_ENABLED)
    ->setWebsiteIds([$website->getId()])
    ->setCateroryIds([])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$product = $productRepository->save($product);


/**
 * createProductStoreValues
 */
foreach ($stores as $store) {
    $product->addAttributeUpdate('status', AttributeStatus::STATUS_ENABLED, $store->getId());
}

/**
 * UnSync Product status values
 */
$connection = $resourceConnection->getConnection();
$connection->query(
    sprintf(
        'UPDATE %s SET `value` = %d ' .
        'WHERE `%s` = %d ' .
        'AND `store_id`= %d ' .
        'AND `attribute_id` = ' .
        '(SELECT `ea`.`attribute_id` FROM %s ea WHERE `ea`.`attribute_code` = "status" LIMIT 1)',
        $resourceConnection->getTableName('catalog_product_entity_int'),
        AttributeStatus::STATUS_DISABLED,
        $productLinkField,
        $product->getData($productLinkField),
        $storeOne->getId(),
        $resourceConnection->getTableName('eav_attribute')
    )
);

/**
 * Opt in "require sync flag" for WebsiteAttributeValueSynchronizer
 */
$flagManager->saveFlag(
    WebsiteAttributesSynchronizer::FLAG_NAME,
    WebsiteAttributesSynchronizer::FLAG_REQUIRES_SYNCHRONIZATION
);
