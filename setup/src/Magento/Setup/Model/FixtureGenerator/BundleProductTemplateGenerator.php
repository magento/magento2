<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\FixtureGenerator;

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;

/**
 * Bundle product template generator. Return newly created bundle product for specified attribute set
 * with default values for product attributes
 */
class BundleProductTemplateGenerator implements TemplateEntityGeneratorInterface
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
     * @var OptionInterfaceFactory
     */
    private $optionFactory;

    /**
     * @var LinkInterfaceFactory
     */
    private $linkFactory;

    /**
     * @param ProductFactory $productFactory
     * @param array $fixture
     * @param OptionInterfaceFactory $optionFactory
     * @param LinkInterfaceFactory $linkFactory
     */
    public function __construct(
        ProductFactory $productFactory,
        array $fixture,
        OptionInterfaceFactory $optionFactory,
        LinkInterfaceFactory $linkFactory
    ) {
        $this->fixture = $fixture;
        $this->productFactory = $productFactory;
        $this->optionFactory = $optionFactory;
        $this->linkFactory = $linkFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateEntity()
    {
        $product = $this->getProductTemplate(
            $this->fixture['attribute_set_id']
        );
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
        $bundleOptions = $this->fixture['_bundle_options'];
        $bundleProductsPerOption = $this->fixture['_bundle_products_per_option'];
        $bundleVariationSkuPattern = $this->fixture['_bundle_variation_sku_pattern'];
        $productRandomizerNumber = crc32(mt_rand(1, PHP_INT_MAX));
        $bundleProduct = $this->productFactory->create([
            'data' => [
                'attribute_set_id' => $attributeSet,
                'type_id' => Type::TYPE_BUNDLE,
                'name' => 'template name' . $productRandomizerNumber,
                'url_key' => 'template-url' . $productRandomizerNumber,
                'sku' => 'template_sku' . $productRandomizerNumber,
                'price' => 10,
                'visibility' => Visibility::VISIBILITY_BOTH,
                'status' => Status::STATUS_ENABLED,
                'website_ids' => [1],
                'category_ids' => isset($this->fixture['category_ids']) ? [2] : null,
                'weight' => 1,
                'description' => 'description',
                'short_description' => 'short description',
                'tax_class_id' => 2, //'taxable goods',
                'price_type' => \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED,
                'price_view' => 1,
                'stock_data' => [
                    'use_config_manage_stock' => 1,
                    'qty' => 100500,
                    'is_qty_decimal' => 0,
                    'is_in_stock' => 1
                ],
                'can_save_bundle_selections' => true,
                'affect_bundle_product_selections' => true,

            ]
        ]);

        $bundleProductOptions = [];
        $variationN = 0;
        for ($i = 1; $i <= $bundleOptions; $i++) {
            $option = $this->optionFactory->create(['data' => [
                'title' => 'Bundle Product Items ' . $i,
                'default_title' => 'Bundle Product Items ' . $i,
                'type' => 'select',
                'required' => 1,
                'delete' => '',
                'position' => $bundleOptions - $i,
                'option_id' => '',
            ]]);
            $option->setSku($bundleProduct->getSku());
            $option->setOptionId(null);

            $links = [];
            for ($linkN = 1; $linkN <= $bundleProductsPerOption; $linkN++) {
                $variationN++;
                $link = $this->linkFactory->create(['data' => [
                    'sku' => sprintf($bundleVariationSkuPattern, $variationN),
                    'qty' => 1,
                    'can_change_qty' => 1,
                    'position' => $linkN - 1,
                    'price_type' => 0,
                    'price' => 0.0,
                    'option_id' => '',
                    'is_default' => $linkN === 1,
                ]]);
                $links[] = $link;
            }
            $option->setProductLinks($links);
            $bundleProductOptions[] = $option;
        }

        $extension = $bundleProduct->getExtensionAttributes();
        $extension->setBundleProductOptions($bundleProductOptions);
        $bundleProduct->setExtensionAttributes($extension);
        // Need for set "has_options" field
        $bundleProduct->setBundleOptionsData($bundleProductOptions);
        $bundleSelections = array_map(function ($option) {
            return array_map(function ($link) {
                return $link->getData();
            }, $option->getProductLinks());
        }, $bundleProductOptions);
        $bundleProduct->setBundleSelectionsData($bundleSelections);

        return $bundleProduct;
    }
}
