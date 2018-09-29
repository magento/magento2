<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Api\Data\LinkInterface;
use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterface;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Registry;
use Magento\Quote\Model\ResourceModel\Quote\Item as QuoteItem;
use Magento\Store\Model\Website;
use Magento\TestFramework\Helper\Bootstrap;

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->create(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);

/** @var Website $website */
$website = Bootstrap::getObjectManager()->create(Website::class);
$website->load('us_website', 'code');
$websiteIds = [$website->getId()];

$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$productId = 10;

/** @var $product Product */
$product = Bootstrap::getObjectManager()->create(Product::class);
$product->setTypeId(Type::TYPE_SIMPLE)
    ->setId($productId)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds($websiteIds)
    ->setName('Simple product ' . $productId)
    ->setSku('simple_' . $productId)
    ->setPrice($productId)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$product = $productRepository->save($product);

// Remove any previously created product with the same id.
/** @var Registry $registry */
$registry = Bootstrap::getObjectManager()->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
try {
    $productToDelete = $productRepository->getById(1);
    $productRepository->delete($productToDelete);

    /** @var QuoteItem $itemResource */
    $itemResource = Bootstrap::getObjectManager()->get(QuoteItem::class);
    $itemResource->getConnection()->delete(
        $itemResource->getMainTable(),
        'product_id = ' . $productToDelete->getId()
    );
} catch (\Exception $e) {
    // Nothing to remove
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

$bundleProductId = 3;

/** @var Product $bundleProduct */
$bundleProduct = Bootstrap::getObjectManager()->create(Product::class);
$bundleProduct->setTypeId(Type::TYPE_BUNDLE)
    ->setId($bundleProductId)
    ->setWebsiteIds($websiteIds)
    ->setAttributeSetId(4)
    ->setName('Bundle Product')
    ->setSku('bundle')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->setPriceView(1)
    ->setPriceType(1)
    ->setShipmentType(1)
    ->setPrice(10.0)
    ->setBundleOptionsData(
        [
            [
                'title' => 'Bundle Product Items',
                'default_title' => 'Bundle Product Items',
                'type' => 'select',
                'required' => 1,
                'delete' => '',
            ],
        ]
    )
    ->setBundleSelectionsData(
        [
            [
                [
                    'product_id' => $product->getId(),
                    'selection_qty' => 1,
                    'selection_can_change_qty' => 1,
                    'delete' => '',
                ],
            ],
        ]
    );
if ($bundleProduct->getBundleOptionsData()) {
    $options = [];
    foreach ($bundleProduct->getBundleOptionsData() as $key => $optionData) {
        if (!(bool)$optionData['delete']) {
            /** @var OptionInterface $option */
            $option = Bootstrap::getObjectManager()->create(OptionInterfaceFactory::class)
                ->create(['data' => $optionData]);
            $option->setSku($bundleProduct->getSku());
            $option->setOptionId(null);
            $links = [];
            $bundleLinks = $bundleProduct->getBundleSelectionsData();
            if (!empty($bundleLinks[$key])) {
                foreach ($bundleLinks[$key] as $linkData) {
                    if (!(bool)$linkData['delete']) {
                        /** @var LinkInterface $link */
                        $link = Bootstrap::getObjectManager()->create(LinkInterfaceFactory::class)
                            ->create(['data' => $linkData]);
                        $linkProduct = $productRepository->getById($linkData['product_id']);
                        $link->setSku($linkProduct->getSku());
                        $link->setQty($linkData['selection_qty']);
                        if (isset($linkData['selection_can_change_qty'])) {
                            $link->setCanChangeQuantity($linkData['selection_can_change_qty']);
                        }
                        $links[] = $link;
                    }
                }
                $option->setProductLinks($links);
                $options[] = $option;
            }
        }
    }
    $extension = $bundleProduct->getExtensionAttributes();
    $extension->setBundleProductOptions($options);
    $bundleProduct->setExtensionAttributes($extension);
}
$bundleProduct->save();

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = Bootstrap::getObjectManager()
                                    ->create(CategoryLinkManagementInterface::class);

$categoryLinkManagement->assignProductToCategories(
    $bundleProduct->getSku(),
    [2]
);
