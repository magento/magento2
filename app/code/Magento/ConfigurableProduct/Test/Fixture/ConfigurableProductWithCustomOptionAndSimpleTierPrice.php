<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Fixture;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterface;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as ConfigurableOptionsFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Group;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Registry;
use Magento\Quote\Model\ResourceModel\Quote\Item as QuoteItemResource;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\ItemFactory as StockItemModelFactory;
use Magento\CatalogInventory\Model\Indexer\Stock\Processor as StockIndexerProcessor;
use Magento\CatalogInventory\Model\Stock\StatusFactory as StockStatusFactory;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;

/**
 * Programmatic equivalent of legacy integration fixtures:
 * product_configurable + custom option + special/tier prices on child simples.
 *
 * @see dev/tests/integration/testsuite/Magento/ConfigurableProduct/_files/product_configurable.php
 * @see dev/tests/integration/testsuite/Magento/ConfigurableProduct/_files/product_configurable_with_custom_option_type_text.php
 * @see dev/tests/integration/testsuite/Magento/ConfigurableProduct/_files/configurable_product_with_custom_option_and_simple_tier_price.php
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class ConfigurableProductWithCustomOptionAndSimpleTierPrice implements RevertibleDataFixtureInterface
{
    private const DATA_KEY_CREATED_TEST_CONFIGURABLE = 'created_test_configurable_attribute';

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAttributeRepositoryInterface $productAttributeRepository
     * @param ProductAttributeInterfaceFactory $productAttributeFactory
     * @param AttributeFactory $catalogAttributeFactory
     * @param EavSetup $eavSetup
     * @param EavConfig $eavConfig
     * @param ConfigurableOptionsFactory $configurableOptionsFactory
     * @param ProductCustomOptionInterfaceFactory $customOptionFactory
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ProductTierPriceExtensionFactory $tierPriceExtensionFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param Registry $registry
     * @param IndexerRegistry $indexerRegistry
     * @param QuoteItemResource $quoteItemResource
     * @param ProductFactory $productFactory
     * @param StockItemModelFactory $stockItemModelFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockStatusFactory $stockStatusFactory
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private ProductAttributeRepositoryInterface $productAttributeRepository,
        private ProductAttributeInterfaceFactory $productAttributeFactory,
        private AttributeFactory $catalogAttributeFactory,
        private EavSetup $eavSetup,
        private EavConfig $eavConfig,
        private ConfigurableOptionsFactory $configurableOptionsFactory,
        private ProductCustomOptionInterfaceFactory $customOptionFactory,
        private ProductTierPriceInterfaceFactory $tierPriceFactory,
        private ProductTierPriceExtensionFactory $tierPriceExtensionFactory,
        private WebsiteRepositoryInterface $websiteRepository,
        private CategoryLinkManagementInterface $categoryLinkManagement,
        private Registry $registry,
        private IndexerRegistry $indexerRegistry,
        private QuoteItemResource $quoteItemResource,
        private ProductFactory $productFactory,
        private StockItemModelFactory $stockItemModelFactory,
        private StockItemRepositoryInterface $stockItemRepository,
        private StockStatusFactory $stockStatusFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): DataObject
    {
        $this->ensureVarcharAttribute();
        $createdTestConfigurable = $this->ensureTestConfigurableAttribute();

        $this->createConfigurableWithSimpleProducts();
        $this->addCustomTextOptionToConfigurable();
        $this->applySpecialPriceAndTierPrices();

        return new DataObject(
            [
                self::DATA_KEY_CREATED_TEST_CONFIGURABLE => $createdTestConfigurable,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function revert(DataObject $data): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);

        foreach (['simple_10', 'simple_20', 'configurable'] as $sku) {
            try {
                $product = $this->productRepository->get($sku, true);
                $stockStatus = $this->stockStatusFactory->create();
                $stockStatus->load($product->getId(), 'product_id');
                $stockStatus->delete();
                if ($product->getId()) {
                    $this->productRepository->delete($product);
                }
            } catch (NoSuchEntityException) {
                // already removed
            }
        }

        if ($data->getData(self::DATA_KEY_CREATED_TEST_CONFIGURABLE)) {
            $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
            if ($attribute instanceof AbstractAttribute && $attribute->getId()) {
                $attribute->delete();
            }
            $this->eavConfig->clear();
        }

        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * Ensures varchar_attribute exists (used on simple products in legacy data).
     *
     * @return void
     */
    private function ensureVarcharAttribute(): void
    {
        $entityType = $this->eavSetup->getEntityTypeId(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $attribute = $this->catalogAttributeFactory->create();
        if ($attribute->loadByCode($entityType, 'varchar_attribute')->getAttributeId()) {
            return;
        }
        $attribute->setData(
            [
                'attribute_code' => 'varchar_attribute',
                'entity_type_id' => $entityType,
                'is_global' => 1,
                'is_user_defined' => 1,
                'frontend_input' => 'text',
                'is_unique' => 0,
                'is_required' => 0,
                'is_searchable' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_comparable' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 0,
                'is_used_for_promo_rules' => 0,
                'is_html_allowed_on_front' => 1,
                'is_visible_on_front' => 0,
                'used_in_product_listing' => 1,
                'used_for_sort_by' => 0,
                'frontend_label' => ['Varchar Attribute'],
                'backend_type' => 'varchar',
            ]
        );
        $attribute->save();
        $this->eavSetup->addAttributeToGroup(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'Default',
            'General',
            $attribute->getId()
        );
    }

    /**
     * Ensures test_configurable select attribute exists and is on the Default attribute set.
     *
     * @return bool True if this run created the attribute (for revert cleanup).
     */
    private function ensureTestConfigurableAttribute(): bool
    {
        $entityTypeId = $this->eavSetup->getEntityTypeId(Product::ENTITY);
        $attributeSetId = $this->eavSetup->getAttributeSetId($entityTypeId, 'Default');
        $groupId = $this->eavSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

        $created = false;
        try {
            $attribute = $this->productAttributeRepository->get('test_configurable');
        } catch (NoSuchEntityException) {
            $created = true;
            $attributeModel = $this->productAttributeFactory->create();
            $attributeModel->setData(
                [
                    'attribute_code' => 'test_configurable',
                    'entity_type_id' => $entityTypeId,
                    'is_global' => 1,
                    'is_user_defined' => 1,
                    'frontend_input' => 'select',
                    'is_unique' => 0,
                    'is_required' => 0,
                    'is_searchable' => 0,
                    'is_visible_in_advanced_search' => 0,
                    'is_comparable' => 0,
                    'is_filterable' => 0,
                    'is_filterable_in_search' => 0,
                    'is_used_for_promo_rules' => 0,
                    'is_html_allowed_on_front' => 1,
                    'is_visible_on_front' => 0,
                    'used_in_product_listing' => 0,
                    'used_for_sort_by' => 0,
                    'frontend_label' => ['Test Configurable'],
                    'backend_type' => 'int',
                    'option' => [
                        'value' => ['option_0' => ['Option 1'], 'option_1' => ['Option 2']],
                        'order' => ['option_0' => 1, 'option_1' => 2],
                    ],
                ]
            );
            $attribute = $this->productAttributeRepository->save($attributeModel);
        }

        $attributeId = (int) $attribute->getAttributeId();
        $this->eavSetup->addAttributeToGroup(Product::ENTITY, $attributeSetId, $groupId, $attributeId);
        $this->eavConfig->clear();

        return $created;
    }

    /**
     * Creates simple_10, simple_20, and configurable products (legacy IDs and SKUs).
     *
     * @return void
     */
    private function createConfigurableWithSimpleProducts(): void
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'test_configurable');
        /** @var AttributeOptionInterface[] $options */
        $options = $attribute->getOptions();
        $attributeSetId = (int) $this->eavSetup->getAttributeSetId(
            $this->eavSetup->getEntityTypeId(Product::ENTITY),
            'Default'
        );
        $associatedProductIds = [];
        $attributeValues = [];
        $productIds = [10, 20];
        array_shift($options);

        foreach ($options as $option) {
            $productId = (int) array_shift($productIds);
            $product = $this->productFactory->create();
            $product->setTypeId(Type::TYPE_SIMPLE)
                ->setId($productId)
                ->setAttributeSetId($attributeSetId)
                ->setWebsiteIds([1])
                ->setName('Configurable Option' . $option->getLabel())
                ->setSku('simple_' . $productId)
                ->setPrice($productId)
                ->setTestConfigurable($option->getValue())
                ->setVarcharAttribute('varchar' . $productId)
                ->setVisibility(Visibility::VISIBILITY_NOT_VISIBLE)
                ->setStatus(Status::STATUS_ENABLED)
                ->setStockData(
                    [
                        'use_config_manage_stock' => 1,
                        'qty' => 100,
                        'is_qty_decimal' => 0,
                        'is_in_stock' => 1,
                    ]
                );
            $product = $this->productRepository->save($product);

            $stockItem = $this->stockItemModelFactory->create();
            $stockItem->load($productId, 'product_id');
            if (!$stockItem->getProductId()) {
                $stockItem->setProductId($productId);
            }
            $stockItem->setUseConfigManageStock(1);
            $stockItem->setQty(1000);
            $stockItem->setIsQtyDecimal(0);
            $stockItem->setIsInStock(1);
            $this->stockItemRepository->save($stockItem);

            $attributeValues[] = [
                'label' => 'test',
                'attribute_id' => $attribute->getId(),
                'value_index' => $option->getValue(),
            ];
            $associatedProductIds[] = (int) $product->getId();
        }

        $this->removeConflictingConfigurableProduct();

        $configurableProduct = $this->productFactory->create();
        $configurableAttributesData = [
            [
                'attribute_id' => $attribute->getId(),
                'code' => $attribute->getAttributeCode(),
                'label' => $attribute->getStoreLabel(),
                'position' => '0',
                'values' => $attributeValues,
            ],
        ];
        $configurableOptions = $this->configurableOptionsFactory->create($configurableAttributesData);
        $extensionConfigurableAttributes = $configurableProduct->getExtensionAttributes();
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
        $extensionConfigurableAttributes->setConfigurableProductLinks($associatedProductIds);
        $configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);

        $configurableProduct->setTypeId(Configurable::TYPE_CODE)
            ->setId(1)
            ->setAttributeSetId($attributeSetId)
            ->setWebsiteIds([1])
            ->setName('Configurable Product')
            ->setSku('configurable')
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

        $this->productRepository->save($configurableProduct);
        $this->categoryLinkManagement->assignProductToCategories($configurableProduct->getSku(), [2]);
    }

    /**
     * Removes product ID 1 if present (legacy fixture behavior).
     *
     * @return void
     */
    private function removeConflictingConfigurableProduct(): void
    {
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', true);
        try {
            $productToDelete = $this->productRepository->getById(1);
            $this->productRepository->delete($productToDelete);
            $this->quoteItemResource->getConnection()->delete(
                $this->quoteItemResource->getMainTable(),
                'product_id = ' . (int) $productToDelete->getId()
            );
            $this->indexerRegistry->get(StockIndexerProcessor::INDEXER_ID)->reindexAll();
        } catch (NoSuchEntityException) {
            // nothing to remove
        }
        $this->registry->unregister('isSecureArea');
        $this->registry->register('isSecureArea', false);
    }

    /**
     * Adds fixed-price custom text option to configurable (legacy option setup).
     *
     * @return void
     */
    private function addCustomTextOptionToConfigurable(): void
    {
        $product = $this->productRepository->get('configurable');
        $createdOption = $this->customOptionFactory->create(
            [
                'data' => [
                    'is_require' => 0,
                    'sku' => 'option-1',
                    'title' => 'Option 1',
                    'type' => ProductCustomOptionInterface::OPTION_TYPE_AREA,
                    'price' => 15,
                    'price_type' => 'fixed',
                ],
            ]
        );
        $createdOption->setProductSku($product->getSku());
        $product->setOptions([$createdOption]);
        $this->productRepository->save($product);
    }

    /**
     * Applies special price to simple_10 and percentage tier to simple_20.
     *
     * @return void
     */
    private function applySpecialPriceAndTierPrices(): void
    {
        $firstSimple = $this->productRepository->get('simple_10');
        $firstSimple->setSpecialPrice(9);
        $this->productRepository->save($firstSimple);

        $secondSimple = $this->productRepository->get('simple_20');
        $tierPriceExtensionAttribute = $this->tierPriceExtensionFactory->create(
            ['data' => ['website_id' => $this->websiteRepository->get('admin')->getId(), 'percentage_value' => 25]]
        );
        $tierPrices = [];
        $tierPrices[] = $this->tierPriceFactory
            ->create(['data' => ['customer_group_id' => Group::CUST_GROUP_ALL, 'qty' => 1]])
            ->setExtensionAttributes($tierPriceExtensionAttribute);
        $secondSimple->setTierPrices($tierPrices);
        $this->productRepository->save($secondSimple);
    }
}
