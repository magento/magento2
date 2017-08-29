<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Helper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryLinkManagement;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Setup\CategorySetup;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as TypeConfigurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class DataTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataTest extends AbstractController
{
    const SWATCH_ATTRIBUTE_NAME = 'test_swatch_attribute';
    const CATEGORY_NAME = 'test_category';
    const CATEGORY_ID = 123456;
    const CONFIGURABLE_PRODUCT_NAME = 'test_configurable_product';
    const SIMPLE_PRODUCT_NAME = 'test_simple_product';

    /**
     * Fill fixtures.
     *
     * We need to fill fixtures exactly one time, so we do it here.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::imageFixtureRollback();
        self::imageFixture();
        self::attributeFixture();
        self::categoryFixture();
        self::productFixture();
    }

    /**
     * Clear fixtures.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        self::productFixtureRollback();
        self::categoryFixtureRollback();
        self::attributeFixtureRollback();
        self::imageFixtureRollback();
        parent::tearDownAfterClass();
    }

    /**
     * Data provider for testSwatchReplacedByImage.
     *
     * "Add product id" is needed because data provider is called before fixtures,
     * so configurable product id can't be determined in data provider.
     *
     * @return array
     */
    public function swatchReplacedByImageDataProvider()
    {
        return [
            'first page' => [
                'dispatch uri' => 'catalog/product/view/id/',
                'add product id' => true,
            ],
            'second page' => [
                'dispatch uri' => 'catalog/category/view/id/' . self::CATEGORY_ID,
                'add product id' => false,
            ],
        ];
    }

    /**
     * Test correctness of displaying swatch image on frontend.
     *
     * This test consistently renders two pages containing product, that has image swatch.
     *         
     * @param string $dispatchUri
     * @param bool $addProductId
     * @return void
     * @magentoDbIsolation enabled
     * @dataProvider swatchReplacedByImageDataProvider
     */
    public function testSwatchReplacedByImage($dispatchUri, $addProductId)
    {
        if ($addProductId) {
            /** @var ProductRepository $productRepository */
            $productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
            /** @var  TypeConfigurable $configurableProduct */
            $configurableProduct = $productRepository->get(self::CONFIGURABLE_PRODUCT_NAME);
            $dispatchUri .= $configurableProduct->getId();
        }
        $this->dispatch($dispatchUri);
        /** @var string $result */
        $result = $this->getResponse()->getBody();
        $this->assertContains('/magento_image.jpg","label":"option 1"', $result);
    }

    /**
     * Product fixture for testSwatchReplacedByImage.
     *
     * This fixture creates one configurable product with two simple configurations and link it to category.
     * Also it sets an image swatch to first configuration.
     *
     * @return void
     */
    private static function productFixture()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        /** @var $installer CategorySetup */
        $installer = Bootstrap::getObjectManager()->create(CategorySetup::class);
        /** @var AttributeRepository $attributeRepository */
        $attributeRepository = Bootstrap::getObjectManager()->create(AttributeRepository::class);
        /** @var Attribute $attribute */
        $attribute = $attributeRepository->get('catalog_product', self::SWATCH_ATTRIBUTE_NAME);
        /* Create simple products per each option value*/
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        $attributeValues = [];
        $attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
        $associatedProductIds = [];
        array_shift($options); // two options is enough
        // create two configurations (simple products)
        $index = 0;
        foreach ($options as $option) {
            $index++;
            /** @var $product Product */
            $product = Bootstrap::getObjectManager()->create(Product::class);
            $product->setTypeId(Type::TYPE_SIMPLE)
                ->setAttributeSetId($attributeSetId)
                ->setWebsiteIds([1])
                ->setName(self::SIMPLE_PRODUCT_NAME . $index)
                ->setSku(self::SIMPLE_PRODUCT_NAME . $index)
                ->setPrice(10)
                ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
                ->setStatus(Status::STATUS_ENABLED)
                ->setStockData(
                    ['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]
                );
            $product->setData($attribute->getAttributeCode(), $option->getValue());
            $product = $productRepository->save($product);
            // simple products must be in stock
            /** @var Item $stockItem */
            $stockItem = Bootstrap::getObjectManager()->create(Item::class);
            $stockItem->load($product->getId(), 'product_id');
            if (!$stockItem->getProductId()) {
                $stockItem->setProductId($product->getId());
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
        // add image swatch to first simple product
        $fileName = '/m/a/magento_image.jpg';
        $fileLabel = 'Magento image';
        /** @var Product $simpleProduct */
        $simpleProduct = $productRepository->get(self::SIMPLE_PRODUCT_NAME . '1');
        $simpleProduct->setData(
            'media_gallery',
            ['images' => ['swatch_image' => ['file' => $fileName, 'label' => $fileLabel]]]
        );
        $simpleProduct->setData('swatch_image', $fileName);
        $simpleProduct->save();
        // create configurable product
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
        $product->setTypeId(TypeConfigurable::TYPE_CODE)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([1])
            ->setName(self::CONFIGURABLE_PRODUCT_NAME)
            ->setSku(self::CONFIGURABLE_PRODUCT_NAME)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
        $productRepository->save($product);
        // link configurable product to category
        /** @var CategoryLinkManagement $categoryLinkManager */
        $categoryLinkManager = Bootstrap::getObjectManager()->get(CategoryLinkManagement::class);
        $categoryLinkManager->assignProductToCategories(self::CONFIGURABLE_PRODUCT_NAME, [self::CATEGORY_ID]);
    }

    /**
     * Product fixture rollback for testSwatchReplacedByImage.
     *
     * Deletes products created in productFixture.
     *
     * @return void
     */
    private static function productFixtureRollback()
    {
        /** @var Registry $registry */
        $registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        /** @var $productCollection Collection */
        $productCollection = Bootstrap::getObjectManager()->create(Collection::class);
        $productCollection->addFieldToFilter(
            [
                ['attribute' => 'sku', 'like' => self::CONFIGURABLE_PRODUCT_NAME],
                ['attribute' => 'sku', 'like' => self::SIMPLE_PRODUCT_NAME . '%'],
            ]
        );
        foreach ($productCollection as $product) {
            $product->delete();
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Attribute fixture for testSwatchReplacedByImage.
     *
     * Creates swatch attribute with two options and assigns it to default attribute set.
     *
     * @return void
     */
    private static function attributeFixture()
    {
        $eavConfig = Bootstrap::getObjectManager()->get(EavConfig::class);
        /** @var $installer CategorySetup */
        $installer = Bootstrap::getObjectManager()->create(CategorySetup::class);
        $data = [
            'is_required' => 0,
            'source_model' => Table::class,
            'is_visible_on_front' => 1,
            'is_visible_in_advanced_search' => 0,
            'attribute_code' => self::SWATCH_ATTRIBUTE_NAME,
            'backend_type' => 'int',
            'is_searchable' => 0,
            'is_filterable' => 0,
            'is_filterable_in_search' => 0,
            'frontend_label' => self::SWATCH_ATTRIBUTE_NAME,
            'entity_type_id' => $installer->getEntityTypeId('catalog_product'),
            'is_user_defined' => 1,
            'is_global' => 1,
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 1,
            'used_in_product_listing' => 1,
            'frontend_input' => 'select',
            'swatch_input_type' => 'visual',
        ];
        $optionsPerAttribute = 2;
        $data['swatchvisual']['value'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) use ($optionsPerAttribute) {
                $values['option_' . $index] = '#'
                    . str_repeat(
                        dechex(255 * $index / $optionsPerAttribute),
                        3
                    );

                return $values;
            },
            []
        );
        $data['optionvisual']['value'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) {
                $values['option_' . $index] = ['option ' . $index];

                return $values;
            },
            []
        );
        $data['options']['option'] = array_reduce(
            range(1, $optionsPerAttribute),
            function ($values, $index) {
                $values[] = [
                    'label' => 'option ' . $index,
                    'value' => 'option_' . $index
                ];

                return $values;
            },
            []
        );
        $options = [];
        foreach ($data['options']['option'] as $optionData) {
            $options[] = Bootstrap::getObjectManager()->create(AttributeOptionInterface::class)
                ->setLabel($optionData['label'])
                ->setValue($optionData['value']);
        }
        $attribute = Bootstrap::getObjectManager()->create(
            ProductAttributeInterface::class,
            ['data' => $data]
        );
        $attribute->setOptions($options);
        $attribute->save();
        // Assign attribute to attribute set.
        $installer->addAttributeToGroup('catalog_product', 'Default', 'General', $attribute->getId());
        $eavConfig->clear();
    }

    /**
     * Attribute fixture rollback for testSwatchReplacedByImage.
     *
     * Deletes attribute created in attributeFixture.
     *
     * @return void
     */
    private static function attributeFixtureRollback()
    {
        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        /** @var Attribute $attribute */
        $attribute = Bootstrap::getObjectManager()
            ->create(Attribute::class);
        $attribute->loadByCode(4, self::SWATCH_ATTRIBUTE_NAME);
        if ($attribute->getId()) {
            $attribute->delete();
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Category fixture for testSwatchReplacedByImage.
     *
     * Creates one category.
     *
     * @return void
     */
    private static function categoryFixture()
    {
        $category = Bootstrap::getObjectManager()->create(Category::class);
        $category->isObjectNew(true);
        $category->setId(
            self::CATEGORY_ID
        )->setName(
            'Category 1'
        )->setParentId(
            2
        )->setPath(
            '1/2/' . self::CATEGORY_ID
        )->setLevel(
            2
        )->setIsActive(
            true
        )->save();
    }

    /**
     * Category fixture rollback for testSwatchReplacedByImage.
     *
     * Deletes category created in categoryFixture.
     *
     * @return void
     */
    private static function categoryFixtureRollback()
    {
        /** @var Registry $registry */
        $registry = Bootstrap::getObjectManager()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        /** @var $category Category */
        $category = Bootstrap::getObjectManager()->create(Category::class);
        $category->load(self::CATEGORY_ID);
        if ($category->getId()) {
            $category->delete();
        }
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', false);
    }

    /**
     * Image fixture for testSwatchReplacedByImage.
     *
     * Creates media dirs and copies magento_image.jpg to temporary directory.
     *
     * @return void
     */
    private static function imageFixture()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $mediaConfig Config */
        $mediaConfig = $objectManager->get(Config::class);
        /** @var $mediaDirectory WriteInterface */
        $mediaDirectory = $objectManager->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $targetDirPath = $mediaConfig->getBaseMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');
        $targetTmpDirPath = $mediaConfig->getBaseTmpMediaPath() . str_replace('/', DIRECTORY_SEPARATOR, '/m/a/');
        $mediaDirectory->create($targetDirPath);
        $mediaDirectory->create($targetTmpDirPath);
        $targetTmpFilePath = $mediaDirectory->getAbsolutePath() . DIRECTORY_SEPARATOR . $targetTmpDirPath
            . DIRECTORY_SEPARATOR . 'magento_image.jpg';
        copy(__DIR__ . '/../_files/magento_image.jpg', $targetTmpFilePath);
    }

    /**
     * Image fixture rollback for testSwatchReplacedByImage.
     *
     * Deletes directories created in imageFixture.
     *
     * @return void
     */
    private static function imageFixtureRollback()
    {
        /** @var $config Config */
        $config = Bootstrap::getObjectManager()->get(Config::class);
        /** @var WriteInterface $mediaDirectory */
        $mediaDirectory = Bootstrap::getObjectManager()
            ->get(Filesystem::class)
            ->getDirectoryWrite(DirectoryList::MEDIA);
        $mediaDirectory->delete($config->getBaseMediaPath());
        $mediaDirectory->delete($config->getBaseTmpMediaPath());
    }
}
