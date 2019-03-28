<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory as OptionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ResourceConnection;

/**
 * Configurable product template generator. Return newly created configurable product for specified attribute set
 * with default values for product attributes
 */
class ConfigurableProductTemplateGenerator implements TemplateEntityGeneratorInterface
{
    /**
     * @var array
     */
    private $fixture;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var OptionFactory
     */
    private $optionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ProductFactory $productFactory
     * @param array $fixture
     * @param OptionFactory $optionFactory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ProductFactory $productFactory,
        array $fixture,
        OptionFactory $optionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->fixture = $fixture;
        $this->productFactory = $productFactory;
        $this->optionFactory = $optionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function generateEntity()
    {
        $attributeSet = $this->fixture['attribute_set_id'];
        $product = $this->getProductTemplate($attributeSet);

        $product->save();

        return $product;
    }

    /**
     * Get product template
     *
     * @param int $attributeSet
     * @return ProductInterface
     */
    private function getProductTemplate($attributeSet)
    {
        $productRandomizerNumber = crc32(random_int(1, PHP_INT_MAX));
        $product = $this->productFactory->create([
            'data' => [
                'attribute_set_id' => $attributeSet,
                'type_id' => Configurable::TYPE_CODE,
                'name' => 'template name' . $productRandomizerNumber,
                'url_key' => 'template-url' . $productRandomizerNumber,
                'sku' => 'template_sku' . $productRandomizerNumber,
                'meta_description' => 'Configurable Product',
                'meta_keyword' => $productRandomizerNumber,
                'meta_title' => $productRandomizerNumber,
                'price' => 10,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'status' => Status::STATUS_ENABLED,
                'website_ids' => (array)$this->fixture['website_ids'](1, 0),
                'category_ids' => isset($this->fixture['category_ids']) ? [2] : null,
                'weight' => 1,
                'description' => 'description',
                'short_description' => 'short description',
                'tax_class_id' => 2, //'taxable goods',
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100500,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ],
                // Need for set "has_options" field
                'can_save_configurable_attributes' => true,
                'configurable_attributes_data' => $this->fixture['_attributes'],
            ]
        ]);

        $attributes = [];
        foreach ($this->fixture['_attributes'] as $index => $attribute) {
            $attributeValues = [];
            foreach ($attribute['values'] as $value) {
                $attributeValues[] = [
                    'label' => $attribute['name'],
                    'attribute_id' => $attribute['id'],
                    'value_index' => $value
                ];
            }
            $attributes[] = [
                'attribute_id' => $attribute['id'],
                'code' => $attribute['name'],
                'label' => $attribute['name'],
                'position' => $index,
                'values' => $attributeValues,
             ];
        }
        $configurableOptions = $this->optionFactory->create($attributes);
        $extensionConfigurableAttributes = $product->getExtensionAttributes();
        $extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
        $extensionConfigurableAttributes->setConfigurableProductLinks($this->getAssociatedProductIds());
        $product->setExtensionAttributes($extensionConfigurableAttributes);

        return $product;
    }

    /**
     * Get configurable variation ids. Retrieve first simple product id by sku pattern from DB and generate next values
     * for all variations
     *
     * @return array
     */
    private function getAssociatedProductIds()
    {
        $associatedProductIds = [];
        $connection = $this->resourceConnection->getConnection();
        $firstSimpleProductId = $connection->fetchRow(
            $connection->select()
                ->from($this->resourceConnection->getTableName('catalog_product_entity'))
                ->where('sku = ?', $this->fixture['_variation_sku_pattern'])
        )['entity_id'];

        for ($i = 0; $i < $this->fixture['_variation_count']; $i++) {
            $associatedProductIds[] = $firstSimpleProductId + $i;
        }

        return $associatedProductIds;
    }
}
