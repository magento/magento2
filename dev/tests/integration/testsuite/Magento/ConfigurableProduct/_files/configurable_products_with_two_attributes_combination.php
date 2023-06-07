<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\Api\Data\ImageContentInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;
use Magento\Framework\App\Filesystem\DirectoryList;

Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_first.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/ConfigurableProduct/_files/configurable_attribute_second.php'
);

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

/** @var $installer CategorySetup */
$installer = $objectManager->create(CategorySetup::class);

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);
$firstAttribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable_first');
$secondAttribute = $eavConfig->getAttribute(Product::ENTITY, 'test_configurable_second');

/** @var Config $config */
$config = $objectManager->get(Config::class);

/** @var Filesystem $filesystem */
$filesystem = $objectManager->get(Filesystem::class);

/** @var WriteInterface $mediaDirectory */
$mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
$baseTmpMediaPath = $config->getBaseTmpMediaPath();
$mediaDirectory->create($baseTmpMediaPath);

/* Create simple products per each option value*/
/** @var AttributeOptionInterface[] $firstAttributeOptions */
$firstAttributeOptions = $firstAttribute->getOptions();
/** @var AttributeOptionInterface[] $secondAttributeOptions */
$secondAttributeOptions = $secondAttribute->getOptions();

$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$associatedProductIds = [];
$firstAttributeValues =  [];
$secondAttributeValues = [];

$testImagePath = __DIR__ . '/magento_image.jpg';
$mediaImage = $mediaDirectory->getAbsolutePath($baseTmpMediaPath . DIRECTORY_SEPARATOR . 'magento_image.jpg');
$result = $mediaDirectory->getDriver()->filePutContents($mediaImage, file_get_contents($testImagePath));

array_shift($firstAttributeOptions);
array_shift($secondAttributeOptions);
foreach ($firstAttributeOptions as $i => $firstAttributeOption) {
    $firstAttributeValues[] = [
        'label' => 'test first ' . $firstAttributeOption->getValue(),
        'attribute_id' => $firstAttribute->getId(),
        'value_index' => $firstAttributeOption->getValue(),
    ];
    foreach ($secondAttributeOptions as $j => $secondAttributeOption) {
        if ($i == 3 && in_array($j, [0, 1])) {
            $qty = 0;
            $isInStock = 0;
        } else {
            $qty = 100;
            $isInStock = 1;
        }

        $image = '/m/a/magento_image.jpg';

        $product = $objectManager->create(Product::class);
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([1])
            ->setName(
                'Configurable Option ' . $firstAttributeOption->getLabel() . '-' . $secondAttributeOption->getLabel()
            )
            ->setSku('simple_' . $firstAttributeOption->getValue() . '_' . $secondAttributeOption->getValue())
            ->setPrice($firstAttributeOption->getValue() + $secondAttributeOption->getValue())
            ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(
                ['use_config_manage_stock' => 1, 'qty' => $qty, 'is_qty_decimal' => 0, 'is_in_stock' => $isInStock]
            )
            ->setImage($image)
            ->setSmallImage($image)
            ->setThumbnail($image)
            ->setData(
                'media_gallery',
                [
                    'images' => [
                        [
                            'file' => $image,
                            'position' => 1,
                            'label' => 'Image Alt Text',
                            'disabled' => 0,
                            'media_type' => 'image',
                            'content' => [
                                'data' => [
                                    ImageContentInterface::BASE64_ENCODED_DATA => base64_encode(
                                        file_get_contents($testImagePath)
                                    ),
                                    ImageContentInterface::NAME => 'simple_' . $firstAttributeOption->getValue() .
                                        '_' . $secondAttributeOption->getValue() . "_1.jpg",
                                    ImageContentInterface::TYPE => "image/jpeg"
                                ]
                            ]
                        ],
                    ]
                ]
            );
        $customAttributes = [
            $firstAttribute->getAttributeCode() => $firstAttributeOption->getValue(),
            $secondAttribute->getAttributeCode() => $secondAttributeOption->getValue()
        ];
        foreach ($customAttributes as $attributeCode => $attributeValue) {
            $product->setCustomAttributes($customAttributes);
        }

        $product = $productRepository->save($product);
        $associatedProductIds[] = $product->getId();

        /** @var Item $stockItem */
        $stockItem = $objectManager->create(Item::class);
        $stockItem->load($product->getId(), 'product_id');

        if (!$stockItem->getProductId()) {
            $stockItem->setProductId($product->getId());
        }
        $stockItem->setUseConfigManageStock(1);
        $stockItem->setQty($qty);
        $stockItem->setIsQtyDecimal(0);
        $stockItem->setIsInStock($isInStock);
        $stockItem->save();

        $secondAttributeValues[$j] = [
            'label' => 'test second ' . $firstAttributeOption->getValue() . $secondAttributeOption->getValue(),
            'attribute_id' => $secondAttribute->getId(),
            'value_index' => $secondAttributeOption->getValue(),
        ];
    }
}

$indexerProcessor = $objectManager->get(PriceIndexerProcessor::class);
$indexerProcessor->reindexList($associatedProductIds, true);

/** @var $product Product */
$product = $objectManager->create(Product::class);

/** @var Factory $optionsFactory */
$optionsFactory = $objectManager->create(Factory::class);

$configurableAttributesData = [
    [
        'attribute_id' => $firstAttribute->getId(),
        'code' => $firstAttribute->getAttributeCode(),
        'label' => $firstAttribute->getStoreLabel(),
        'position' => '0',
        'values' => $firstAttributeValues,
    ],
    [
        'attribute_id' => $secondAttribute->getId(),
        'code' => $secondAttribute->getAttributeCode(),
        'label' => $secondAttribute->getStoreLabel(),
        'position' => '1',
        'values' => $secondAttributeValues,
    ],
];

$configurableOptions = $optionsFactory->create($configurableAttributesData);
$firstAttributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');

$extensionConfigurableAttributes = $product->getExtensionAttributes();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);

$product->setExtensionAttributes($extensionConfigurableAttributes);

$product->setTypeId(Configurable::TYPE_CODE)
    ->setAttributeSetId($firstAttributeSetId)
    ->setWebsiteIds([1])
    ->setName('Configurable Product 12345')
    ->setSku('configurable_12345')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1])
    ->addImageToMediaGallery(
        $mediaImage,
        ['image', 'small_image', 'thumbnail'],
        false,
        false
    );
$productRepository->cleanCache();
$product = $productRepository->save($product);

$indexerProcessor = $objectManager->get(PriceIndexerProcessor::class);
$indexerProcessor->reindexRow($product->getId(), true);
