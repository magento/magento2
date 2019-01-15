<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory;

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

require __DIR__ . '/configurable_attribute.php';

/** @var ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()
    ->create(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(CategorySetup::class);

/* Create simple products per each option value*/
/** @var AttributeOptionInterface[] $options */
$options = $attribute->getOptions();

$attributeValues = [];
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$associatedProductIds = [];
$productIds = [10, 20];
array_shift($options); //remove the first option which is empty

foreach ($options as $option) {
    /** @var $product Product */
    $product = Bootstrap::getObjectManager()->create(Product::class);
    $productId = array_shift($productIds);
    $product->setTypeId(Type::TYPE_SIMPLE)
        ->setId($productId)
        ->setAttributeSetId($attributeSetId)
        ->setWebsiteIds([1])
        ->setName('Configurable Option' . $option->getLabel())
        ->setSku('simple_' . $productId)
        ->setPrice($productId)
        ->setTestConfigurable($option->getValue())
        ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
        ->setStatus(Status::STATUS_ENABLED)
        ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    $eavAttributeValues = [
        'category_ids' => [2]
        ];
    foreach ($eavAttributeValues as $eavCategoryAttributeCode => $eavCategoryAttributeValues) {
        $product->setCustomAttribute($eavCategoryAttributeCode, $eavCategoryAttributeValues);
    }

    $product = $productRepository->save($product);

    /**
     * @var \Magento\TestFramework\ObjectManager $objectManager
     */
    $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

    /**
     * @var \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory $mediaGalleryEntryFactory
     */

    $mediaGalleryEntryFactory = $objectManager->get(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryInterfaceFactory::class
    );

    /**
     * @var \Magento\Framework\Api\Data\ImageContentInterfaceFactory $imageContentFactory
     */
    $imageContentFactory = $objectManager->get(\Magento\Framework\Api\Data\ImageContentInterfaceFactory::class);
    $imageContent = $imageContentFactory->create();
    $testImagePath = __DIR__ .'/magento_image.jpg';
    $imageContent->setBase64EncodedData(base64_encode(file_get_contents($testImagePath)));
    $imageContent->setType("image/jpeg");
    $imageContent->setName("1.jpg");

    $video = $mediaGalleryEntryFactory->create();
    $video->setDisabled(false);
    $video->setFile('1.jpg');
    $video->setLabel('Video Label');
    $video->setMediaType('external-video');
    $video->setPosition(2);
    $video->setContent($imageContent);

    /**
     * @var ProductAttributeMediaGalleryEntryExtensionFactory $mediaGalleryEntryExtensionFactory
     */
    $mediaGalleryEntryExtensionFactory = $objectManager->get(
        \Magento\Catalog\Api\Data\ProductAttributeMediaGalleryEntryExtensionFactory::class
    );
    $mediaGalleryEntryExtension = $mediaGalleryEntryExtensionFactory->create();

    /**
     * @var \Magento\Framework\Api\Data\VideoContentInterfaceFactory $videoContentFactory
     */
    $videoContentFactory = $objectManager->get(
        \Magento\Framework\Api\Data\VideoContentInterfaceFactory::class
    );
    $videoContent = $videoContentFactory->create();
    $videoContent->setMediaType('external-video');
    $videoContent->setVideoDescription('Video description');
    $videoContent->setVideoProvider('youtube');
    $videoContent->setVideoMetadata('Video Metadata');
    $videoContent->setVideoTitle('Video title');
    $videoContent->setVideoUrl('http://www.youtube.com/v/tH_2PFNmWoga');

    $mediaGalleryEntryExtension->setVideoContent($videoContent);
    $video->setExtensionAttributes($mediaGalleryEntryExtension);

    /**
     * @var \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface $mediaGalleryManagement
     */
    $mediaGalleryManagement = $objectManager->get(
        \Magento\Catalog\Api\ProductAttributeMediaGalleryManagementInterface::class
    );
    $mediaGalleryManagement->create('simple_' . $productId, $video);

    /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
    $stockItem = Bootstrap::getObjectManager()->create(\Magento\CatalogInventory\Model\Stock\Item::class);
    $stockItem->load($productId, 'product_id');

    if (!$stockItem->getProductId()) {
        $stockItem->setProductId($productId);
    }
    $stockItem->setUseConfigManageStock(1);
    $stockItem->setQty(1000);
    $stockItem->setIsQtyDecimal(0);
    $stockItem->setIsInStock(1);
    $stockItem->save();

    $attributeValues[] = [
        'label' => 'test',
        'attribute_id' => $attribute->getId(),
        'value_index' => $option->getValue(),
    ];
    $associatedProductIds[] = $product->getId();
}

/** @var $product Product */
$product = Bootstrap::getObjectManager()->create(Product::class);

/** @var Factory $optionsFactory */
$optionsFactory = Bootstrap::getObjectManager()->create(Factory::class);

$configurableAttributesData = [
    [
        'attribute_id' => $attribute->getId(),
        'code' => $attribute->getAttributeCode(),
        'label' => $attribute->getStoreLabel(),
        'position' => '0',
        'values' => $attributeValues,
    ],
];

$configurableOptions = $optionsFactory->create($configurableAttributesData);

$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

$product->setExtensionAttributes($extensionConfigurableAttributes);

// Remove any previously created product with the same id.
/** @var \Magento\Framework\Registry $registry */
$registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
try {
    $productToDelete = $productRepository->getById(1);
    $productRepository->delete($productToDelete);

    /** @var \Magento\Quote\Model\ResourceModel\Quote\Item $itemResource */
    $itemResource = Bootstrap::getObjectManager()->get(\Magento\Quote\Model\ResourceModel\Quote\Item::class);
    $itemResource->getConnection()->delete(
        $itemResource->getMainTable(),
        'product_id = ' . $productToDelete->getId()
    );
} catch (\Exception $e) {
    // Nothing to remove
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

$product->setTypeId(Configurable::TYPE_CODE)
    ->setId(1)
    ->setAttributeSetId($attributeSetId)
    ->setWebsiteIds([1])
    ->setName('Configurable Product')
    ->setSku('configurable')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setWeight(1)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

$productRepository->save($product);

/** @var \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);

$categoryLinkManagement->assignProductToCategories(
    $product->getSku(),
    [2]
);
