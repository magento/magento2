<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Setup\Model\DataGenerator;
use Magento\Setup\Model\Complex\Pattern;
use Magento\Setup\Model\Complex\Generator;

/**
 * Class ConfigurableProductsFixture
 */
class ConfigurableProductsFixture extends SimpleProductsFixture
{
    /**
     * @var int
     */
    protected $priority = 50;

    /**
     * @var array
     */
    protected $searchConfig;

    /**
     * Variations count.
     *
     * @var int
     */
    protected $variationsCount;

    //@codingStandardsIgnoreStart
    /**
     * Get CSV template headers
     * @SuppressWarnings(PHPMD)
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'sku',
            'store_view_code',
            'attribute_set_code',
            'additional_attributes',
            'product_type',
            'categories',
            'product_websites',
            'color',
            'configurable_variation',
            'cost',
            'country_of_manufacture',
            'created_at',
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update',
            'description',
            'enable_googlecheckout',
            'gallery',
            'gift_message_available',
            'gift_wrapping_available',
            'gift_wrapping_price',
            'has_options',
            'image',
            'image_label',
            'is_returnable',
            'manufacturer',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'minimal_price',
            'msrp',
            'msrp_display_actual_price_type',
            'name',
            'news_from_date',
            'news_to_date',
            'options_container',
            'page_layout',
            'price',
            'quantity_and_stock_status',
            'related_tgtr_position_behavior',
            'related_tgtr_position_limit',
            'required_options',
            'short_description',
            'small_image',
            'small_image_label',
            'special_from_date',
            'special_price',
            'special_to_date',
            'product_online',
            'tax_class_name',
            'thumbnail',
            'thumbnail_label',
            'updated_at',
            'upsell_tgtr_position_behavior',
            'upsell_tgtr_position_limit',
            'url_key',
            'url_path',
            'variations',
            'variations_1382710717',
            'variations_1382710773',
            'variations_1382710861',
            'visibility',
            'weight',
            'qty',
            'min_qty',
            'use_config_min_qty',
            'is_qty_decimal',
            'backorders',
            'use_config_backorders',
            'min_sale_qty',
            'use_config_min_sale_qty',
            'max_sale_qty',
            'use_config_max_sale_qty',
            'is_in_stock',
            'notify_stock_qty',
            'use_config_notify_stock_qty',
            'manage_stock',
            'use_config_manage_stock',
            'use_config_qty_increments',
            'qty_increments',
            'use_config_enable_qty_inc',
            'enable_qty_increments',
            'is_decimal_divided',
            '_related_sku',
            '_related_position',
            '_crosssell_sku',
            '_crosssell_position',
            '_upsell_sku',
            '_upsell_position',
            '_associated_sku',
            '_associated_default_qty',
            '_associated_position',
            '_tier_price_website',
            '_tier_price_customer_group',
            '_tier_price_qty',
            '_tier_price_price',
            '_media_attribute_id',
            '_media_image',
            '_media_label',
            '_media_position',
            '_media_is_disabled',
            '_super_products_sku',
            '_super_attribute_code',
            '_super_attribute_option',
            'configurable_variations',
        ];
    }

    /**
     * @param Closure|mixed $productCategoryClosure
     * @param Closure|mixed $productWebsiteClosure
     * @param Closure|mixed $shortDescriptionClosure
     * @param Closure|mixed $descriptionClosure
     * @param Closure|mixed $priceClosure
     * @param Closure|mixed $attributeSetClosure
     * @param Closure|mixed $additionalAttributesClosure
     * @param string $variationClosure
     * @param string $suffix
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    private function generateConfigurableProduct(
        $productCategoryClosure,
        $productWebsiteClosure,
        $shortDescriptionClosure,
        $descriptionClosure,
        $priceClosure,
        $attributeSetClosure,
        $additionalAttributesClosure,
        $variationClosure,
        $suffix
    )
    {
        return [
            'sku' => $this->getConfigurableProductSkuPattern() . $suffix,
            'store_view_code' => '',
            'attribute_set_code' => $attributeSetClosure,
            'additional_attributes' => $additionalAttributesClosure,
            'product_type' => 'configurable',
            'categories' => $productCategoryClosure,
            'product_websites' => $productWebsiteClosure,
            'color' => '',
            'configurable_variation' => '',
            'cost' => '',
            'country_of_manufacture' => '',
            'created_at' => '2013-10-25 15:12:39',
            'custom_design' => '',
            'custom_design_from' => '',
            'custom_design_to' => '',
            'custom_layout_update' => '',
            'description' => $descriptionClosure,
            'enable_googlecheckout' => '1',
            'gallery' => '',
            'gift_message_available' => '',
            'gift_wrapping_available' => '',
            'gift_wrapping_price' => '',
            'has_options' => '1',
            'image' => '',
            'image_label' => '',
            'is_returnable' => 'no',
            'manufacturer' => '',
            'meta_description' => 'Configurable Product %s <p>Configurable product description %s</p>',
            'meta_keyword' => 'Configurable Product %s',
            'meta_title' => 'Configurable Product %s',
            'minimal_price' => '',
            'msrp' => '',
            'msrp_display_actual_price_type' => 'Use config',
            'name' => 'Configurable Product %s' . $suffix,
            'news_from_date' => '',
            'news_to_date' => '',
            'options_container' => 'Block after Info Column',
            'page_layout' => '',
            'price' => $priceClosure,
            'quantity_and_stock_status' => 'In Stock',
            'related_tgtr_position_behavior' => '',
            'related_tgtr_position_limit' => '',
            'required_options' => '1',
            'short_description' => $shortDescriptionClosure,
            'small_image' => '',
            'small_image_label' => '',
            'special_from_date' => '',
            'special_price' => '',
            'special_to_date' => '',
            'product_online' => '1',
            'tax_class_name' => 'Taxable Goods',
            'thumbnail' => '',
            'thumbnail_label' => '',
            'updated_at' => '2013-10-25 15:12:39',
            'upsell_tgtr_position_behavior' => '',
            'upsell_tgtr_position_limit' => '',
            'url_key' => $this->getUrlKeyPrefix() . "{$suffix}",
            'url_path' => $this->getUrlKeyPrefix() . "{$suffix}",
            'visibility' => 'Catalog, Search',
            'weight' => '',
            'qty' => 333,
            'min_qty' => '0.0000',
            'use_config_min_qty' => '1',
            'is_qty_decimal' => '0',
            'backorders' => '0',
            'use_config_backorders' => '1',
            'min_sale_qty' => '1.0000',
            'use_config_min_sale_qty' => '1',
            'max_sale_qty' => '0.0000',
            'use_config_max_sale_qty' => '1',
            'is_in_stock' => '1',
            'notify_stock_qty' => '',
            'use_config_notify_stock_qty' => '1',
            'manage_stock' => '1',
            'use_config_manage_stock' => '1',
            'use_config_qty_increments' => '1',
            'qty_increments' => '0.0000',
            'use_config_enable_qty_inc' => '1',
            'enable_qty_increments' => '0',
            'is_decimal_divided' => '0',
            '_related_sku' => '',
            '_related_position' => '',
            '_crosssell_sku' => '',
            '_crosssell_position' => '',
            '_upsell_sku' => '',
            '_upsell_position' => '',
            '_associated_sku' => '',
            '_associated_default_qty' => '',
            '_associated_position' => '',
            '_tier_price_website' => '',
            '_tier_price_customer_group' => '',
            '_tier_price_qty' => '',
            '_tier_price_price' => '',
            '_media_attribute_id' => '',
            '_media_image' => '',
            '_media_label' => '',
            '_media_position' => '',
            '_media_is_disabled' => '',
            'configurable_variations' => $variationClosure,
        ];
    }

    /**
     * Get CSV template rows
     *
     * @param Closure|mixed $productCategoryClosure
     * @param Closure|mixed $productWebsiteClosure
     * @param Closure|mixed $shortDescriptionClosure
     * @param Closure|mixed $descriptionClosure
     * @param Closure|mixed $priceClosure
     * @param Closure|mixed $attributeSetClosure
     * @param Closure|mixed $additionalAttributesClosure
     * @param Closure|mixed $variationClosure
     * @param int $optionsNumber
     * @param string $suffix
     *
     * @SuppressWarnings(PHPMD)
     *
     * @return array
     */
    protected function getRows(
        $productCategoryClosure,
        $productWebsiteClosure,
        $shortDescriptionClosure,
        $descriptionClosure,
        $priceClosure,
        $attributeSetClosure,
        $additionalAttributesClosure,
        $variationClosure,
        $optionsNumber,
        $suffix = ''
    )
    {
        $data = [];
        for ($i = 1; $i <= $optionsNumber; $i++) {
            $productData = [
                'sku' => $this->getConfigurableOptionSkuPattern() . "{$i}{$suffix}",
                'store_view_code' => '',
                'attribute_set_code' => $attributeSetClosure,
                'additional_attributes' => $additionalAttributesClosure,
                'product_type' => 'simple',
                'categories' => $productCategoryClosure,
                'product_websites' => $productWebsiteClosure,
                'color' => '',
                'configurable_variation' => "option {$i}",
                'cost' => '',
                'country_of_manufacture' => '',
                'created_at' => '2013-10-25 15:12:32',
                'custom_design' => '',
                'custom_design_from' => '',
                'custom_design_to' => '',
                'custom_layout_update' => '',
                'description' => $descriptionClosure,
                'enable_googlecheckout' => '1',
                'gallery' => '',
                'gift_message_available' => '',
                'gift_wrapping_available' => '',
                'gift_wrapping_price' => '',
                'has_options' => '0',
                'image' => '',
                'image_label' => '',
                'is_returnable' => 'no',
                'manufacturer' => '',
                'meta_description' => 'Configurable Product %s <p>Configurable product description 1</p>',
                'meta_keyword' => 'Configurable Product 1',
                'meta_title' => 'Configurable Product %s',
                'minimal_price' => '',
                'msrp' => '',
                'msrp_display_actual_price_type' => 'Use config',
                'name' => "Configurable Product {$suffix}- %s-option {$i}",
                'news_from_date' => '',
                'news_to_date' => '',
                'options_container' => 'Block after Info Column',
                'page_layout' => '',
                'price' => $priceClosure,
                'quantity_and_stock_status' => 'In Stock',
                'related_tgtr_position_behavior' => '',
                'related_tgtr_position_limit' => '',
                'required_options' => '0',
                'short_description' => $shortDescriptionClosure,
                'small_image' => '',
                'small_image_label' => '',
                'special_from_date' => '',
                'special_price' => '',
                'special_to_date' => '',
                'product_online' => '1',
                'tax_class_name' => 'Taxable Goods',
                'thumbnail' => '',
                'thumbnail_label' => '',
                'updated_at' => '2013-10-25 15:12:32',
                'upsell_tgtr_position_behavior' => '',
                'upsell_tgtr_position_limit' => '',
                'url_key' => $this->getOptionUrlKeyPrefix() . "-{$suffix}-%s-option-{$i}",
                'url_path' => $this->getOptionUrlKeyPrefix() . "-{$suffix}-%s-option-{$i}",
                'variations' => '',
                'variations_1382710717' => '',
                'variations_1382710773' => '',
                'variations_1382710861' => '',
                'visibility' => 'Not Visible Individually',
                'weight' => '1',
                'qty' => '111.0000',
                'min_qty' => '0.0000',
                'use_config_min_qty' => '1',
                'is_qty_decimal' => '0',
                'backorders' => '0',
                'use_config_backorders' => '1',
                'min_sale_qty' => '1.0000',
                'use_config_min_sale_qty' => '1',
                'max_sale_qty' => '0.0000',
                'use_config_max_sale_qty' => '1',
                'is_in_stock' => '1',
                'notify_stock_qty' => '',
                'use_config_notify_stock_qty' => '1',
                'manage_stock' => '1',
                'use_config_manage_stock' => '1',
                'use_config_qty_increments' => '1',
                'qty_increments' => '0.0000',
                'use_config_enable_qty_inc' => '1',
                'enable_qty_increments' => '0',
                'is_decimal_divided' => '0',
                '_related_sku' => '',
                '_related_position' => '',
                '_crosssell_sku' => '',
                '_crosssell_position' => '',
                '_upsell_sku' => '',
                '_upsell_position' => '',
                '_associated_sku' => '',
                '_associated_default_qty' => '',
                '_associated_position' => '',
                '_tier_price_website' => '',
                '_tier_price_customer_group' => '',
                '_tier_price_qty' => '',
                '_tier_price_price' => '',
                '_media_attribute_id' => '',
                '_media_image' => '',
                '_media_label' => '',
                '_media_position' => '',
                '_media_is_disabled' => '',
            ];
            $data[] = $productData;
        }

        $data[] = $this->generateConfigurableProduct(
            $productCategoryClosure,
            $productWebsiteClosure,
            $shortDescriptionClosure,
            $descriptionClosure,
            $priceClosure,
            $attributeSetClosure,
            $additionalAttributesClosure,
            $variationClosure,
            $suffix
        );
        return $data;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD)
     */
    public function execute()
    {
        $configurableProductsCount = $this->getConfigurableProductsValue();
        if (!$configurableProductsCount) {
            return;
        }
        $simpleProductsCount = $this->fixtureModel->getValue('simple_products', 0);
        $maxAmountOfWordsDescription = $this->getSearchConfigValue('max_amount_of_words_description');
        $maxAmountOfWordsShortDescription = $this->getSearchConfigValue('max_amount_of_words_short_description');
        $minAmountOfWordsDescription = $this->getSearchConfigValue('min_amount_of_words_description');
        $minAmountOfWordsShortDescription = $this->getSearchConfigValue('min_amount_of_words_short_description');
        $configurableProductsWithAttributes = [];

        if ($this->getAdditionalConfigurableProductsVariations() === null) {
            $configurableProductsWithAttributes[$configurableProductsCount]
                = $this->getConfigurableProductsVariationsValue();
        } else {
            if (strpos($this->getAdditionalConfigurableProductsVariations(), ',')) {
                $variations = explode(',', $this->getAdditionalConfigurableProductsVariations());
            } else {
                $variations = [$this->getAdditionalConfigurableProductsVariations()];
            }

            foreach ($variations as $variation) {
                $value = explode(':', $variation);
                $configurableProductsWithAttributes[$value[0]] = $value[1];
            }
        }

        foreach ($configurableProductsWithAttributes as $productsCount => $variationsCount) {
            $this->variationsCount = $variationsCount;
            $configurableProductsCount = $productsCount;
            $attributes = $this->getAttributes();
            $searchTerms = $this->getSearchTerms();
            $this->fixtureModel->resetObjectManager();
            $result = $this->getCategoriesAndWebsites();
            $result = array_values($result);
            $dataGenerator = new DataGenerator(realpath(__DIR__ . '/' . 'dictionary.csv'));

            $productWebsiteClosure = function ($index) use ($result) {
                return $result[$index % count($result)][0];
            };
            $productCategoryClosure = function ($index) use ($result) {
                return $result[$index % count($result)][2] . '/' . $result[$index % count($result)][1];
            };
            $shortDescriptionClosure = function ($index)
            use (
                $searchTerms,
                $simpleProductsCount,
                $configurableProductsCount,
                $dataGenerator,
                $maxAmountOfWordsShortDescription,
                $minAmountOfWordsShortDescription
            ) {
                $count = $searchTerms === null
                    ? 0
                    : round(
                        $searchTerms[$index % count($searchTerms)]['count'] * (
                            $configurableProductsCount / ($simpleProductsCount + $configurableProductsCount)
                        )
                    );
                mt_srand($index);
                return $dataGenerator->generate(
                    $minAmountOfWordsShortDescription,
                    $maxAmountOfWordsShortDescription,
                    'shortDescription-' . $index
                ) . ($index <= ($count * count($searchTerms)) ? ' '
                    . $searchTerms[$index % count($searchTerms)]['term'] : '');
            };
            $descriptionClosure = function ($index)
            use (
                $searchTerms,
                $simpleProductsCount,
                $configurableProductsCount,
                $dataGenerator,
                $maxAmountOfWordsDescription,
                $minAmountOfWordsDescription
            ) {
                $count = $searchTerms === null
                    ? 0
                    : round(
                        $searchTerms[$index % count($searchTerms)]['count'] * (
                            $configurableProductsCount / ($simpleProductsCount + $configurableProductsCount)
                        )
                    );
                mt_srand($index);
                return $dataGenerator->generate(
                    $minAmountOfWordsDescription,
                    $maxAmountOfWordsDescription,
                    'description-' . $index
                ) . ($index <= ($count * count($searchTerms))
                    ? ' ' . $searchTerms[$index % count($searchTerms)]['term'] : '');
            };
            $priceClosure = function ($index) {
                mt_srand($index);
                switch (mt_rand(0, 3)) {
                    case 0:
                        return 9.99;
                    case 1:
                        return 5;
                    case 2:
                        return 1;
                    case 3:
                        return mt_rand(1, 10000) / 10;
                }
            };
            $attributeSetClosure = $this->getAttributeSetClosure($attributes, $result);
            $variationClosure = $this->getVariationsClosure($attributes, $result, $variationsCount);
            $additionalAttributesClosure = $this->getAdditionalAttributesClosure($attributes, $result);
            /**
             * Create configurable products
             */
            $pattern = new Pattern();
            $pattern->setHeaders($this->getHeaders());
            $pattern->setRowsSet(
                $this->getRows(
                    $productCategoryClosure,
                    $productWebsiteClosure,
                    $shortDescriptionClosure,
                    $descriptionClosure,
                    $priceClosure,
                    $attributeSetClosure,
                    $additionalAttributesClosure,
                    $variationClosure,
                    $variationsCount
                )
            );

            /** @var \Magento\ImportExport\Model\Import $import */
            $import = $this->fixtureModel->getObjectManager()->create(
                \Magento\ImportExport\Model\Import::class,
                [
                    'data' => [
                        'entity' => 'catalog_product',
                        'behavior' => 'append',
                        'validation_strategy' => 'validation-stop-on-errors',
                    ],
                ]
            );

            $source = $this->fixtureModel->getObjectManager()->create(
                Generator::class,
                ['rowPattern' => $pattern, 'count' => $configurableProductsCount]
            );
            // it is not obvious, but the validateSource() will actually save import queue data to DB
            if (!$import->validateSource($source)) {
                throw new \Exception($import->getFormatedLogTrace());
            }
            // this converts import queue into actual entities
            if (!$import->importSource()) {
                throw new \Exception($import->getFormatedLogTrace());
            }
        }
    }
    // @codingStandardsIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating configurable products';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [
            'configurable_products' => 'Configurable products',
        ];
    }

    /**
     * @override
     * @return array
     */
    protected function getCategoriesAndWebsites()
    {
        /** @var \Magento\Store\Model\StoreManager $storeManager */
        $storeManager = $this->fixtureModel->getObjectManager()->get(\Magento\Store\Model\StoreManager::class);
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $this->fixtureModel->getObjectManager()->create(\Magento\Catalog\Model\Category::class);

        $result = [];
        //Get all websites
        $websites = $storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteCode = $website->getCode();
            //Get all groups
            $websiteGroups = $website->getGroups();
            foreach ($websiteGroups as $websiteGroup) {
                $websiteGroupRootCategory = $websiteGroup->getRootCategoryId();
                $category->load($websiteGroupRootCategory);
                $categoryResource = $category->getResource();
                $rootCategoryName = $category->getName();
                //Get all categories
                $resultsCategories = $categoryResource->getAllChildren($category);
                foreach ($resultsCategories as $resultsCategory) {
                    $category->load($resultsCategory);
                    $structure = explode('/', $category->getPath());
                    $pathSize = count($structure);
                    if ($pathSize > 1) {
                        $path = [];
                        for ($i = 1; $i < $pathSize; $i++) {
                            $path[] = $category->load($structure[$i])->getName();
                        }
                        array_shift($path);
                        $resultsCategoryName = implode('/', $path);
                    } else {
                        $resultsCategoryName = $category->getName();
                    }
                    //Deleted root categories
                    if (trim($resultsCategoryName) != '') {
                        $result[$resultsCategory] = [$websiteCode, $resultsCategoryName, $rootCategoryName];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get configurable products value.
     *
     * @return int
     */
    protected function getConfigurableProductsValue()
    {
        return $this->fixtureModel->getValue('configurable_products', 0);
    }

    /**
     * Get configurable products variations value.
     *
     * @return int
     */
    protected function getConfigurableProductsVariationsValue()
    {
        return $this->fixtureModel->getValue('configurable_products_variation', 3);
    }

    /**
     * Get attribute set closure
     *
     * @param array $attributes
     * @param array $result
     * @return \Closure
     */
    protected function getAttributeSetClosure(array $attributes, array $result)
    {
        return function ($index) use ($attributes, $result) {
            mt_srand($index);
            $attributeSet =  (count(array_keys($attributes)) > (($index - 1) % count($result))
                ? array_keys($attributes)[mt_rand(0, count(array_keys($attributes)) - 1)] : 'Default');
            return $attributeSet;
        };
    }

    /**
     * Get additional attributes closure.
     *
     * @param array $attributes
     * @param array $result
     * @return \Closure
     */
    protected function getAdditionalAttributesClosure(array $attributes, array $result)
    {
        return function ($index, $variationIndex) use ($attributes, $result) {
            $attributeValues = '';
            mt_srand($index);
            $attributeSetCode = (count(array_keys($attributes)) > (($index - 1) % count($result))
                ? array_keys($attributes)[mt_rand(0, count(array_keys($attributes)) - 1)] : 'Default');
            if ($attributeSetCode !== 'Default') {
                foreach ($attributes[$attributeSetCode] as $attribute) {
                    $attributeValues = $attributeValues . $attribute['name'] . "=" .
                        $attribute['values'][$variationIndex %  count($attribute['values'])] . ",";
                }
            }
            return trim($attributeValues, ",");
        };
    }

    /**
     * Get variations closure.
     *
     * @param array $attributes
     * @param array $result
     * @param int $variationCount
     * @return \Closure
     */
    protected function getVariationsClosure(array $attributes, array $result, $variationCount)
    {
        return function ($index, $variationIndex) use ($attributes, $result, $variationCount) {
            mt_srand($index);
            $attributeSetCode = (count(array_keys($attributes)) > (($index - 1) % count($result))
                ? array_keys($attributes)[mt_rand(0, count(array_keys($attributes)) - 1)] : 'Default');
            $skus = [];
            for ($i = 1; $i <= $variationCount; $i++) {
                $skus[] = 'sku=' . sprintf($this->getConfigurableOptionSkuPattern(), $index) . $i;
            }
            $values = [];
            if ($attributeSetCode == 'Default') {
                for ($i = 1; $i <= $variationCount; $i++) {
                    $values[] = 'configurable_variation=option ' . $i;
                }
            } else {
                for ($i = $variationCount; $i > 0; $i--) {
                    $attributeValues = '';
                    foreach ($attributes[$attributeSetCode] as $attribute) {
                        $attributeValues = $attributeValues . $attribute['name'] . "=" .
                            $attribute['values'][($variationIndex - $i) % count($attribute['values'])] . ",";
                    }
                    $values [] = $attributeValues;
                }
            }
            $variations = [];
            for ($i = 0; $i < $variationCount; $i++) {
                $variations[] = trim(implode(",", [$skus[$i], $values[$i]]), ",");
            }
            return implode("|", $variations);
        };
    }

    /**
     * Get configurable product sku pattern.
     *
     * @return string
     */
    private function getConfigurableProductSkuPattern()
    {
        return 'Configurable Product ' . $this->getConfigurableProductPrefix() . ' %s';
    }

    /**
     * Get configurable option sku pattern.
     *
     * @return string
     */
    protected function getConfigurableOptionSkuPattern()
    {
        return 'Configurable Product ' . $this->getConfigurableProductPrefix() . '%s-option';
    }

    /**
     * Get url key prefix.
     *
     * @return string
     */
    private function getUrlKeyPrefix()
    {
        return 'configurable-product' . $this->getConfigurableProductPrefix() . '-%s';
    }

    /**
     * Get option url key prefix.
     *
     * @return string
     */
    private function getOptionUrlKeyPrefix()
    {
        return 'simple-of-configurable-product' . $this->getConfigurableProductPrefix();
    }

    /**
     * Get additional configurations for configurable products.
     *
     * @return string|null
     */
    private function getAdditionalConfigurableProductsVariations()
    {
        return $this->fixtureModel->getValue('configurable_products_variations', null);
    }

    /**
     * Get configurable product prefix.
     *
     * @return string
     */
    protected function getConfigurableProductPrefix()
    {
        return '';
    }
}
