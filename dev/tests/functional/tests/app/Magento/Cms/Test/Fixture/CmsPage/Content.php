<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Fixture\CmsPage;

use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Cms\Test\Fixture\CmsBlock;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Prepare content for cms page.
 */
class Content implements FixtureInterface
{
    /**
     * Content data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Fixture params.
     *
     * @var array
     */
    protected $params;

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @constructor
     * @param array $params
     * @param array $data
     * @param FixtureFactory $fixtureFactory
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $this->data = $data;
        if (isset($data['widget']['preset'])) {
            $this->data['widget']['preset'] = $this->getPreset($data['widget']['preset']);
            foreach ($this->data['widget']['preset'] as $key => $widget) {
                if (isset($widget['chosen_option']['category_path'])
                    && !isset($widget['chosen_option']['filter_sku'])
                ) {
                    $category = $this->createCategory($widget);
                    $categoryName = $category->getData('name');
                    $this->data['widget']['preset'][$key]['chosen_option']['category_path'] = $categoryName;
                }
                if (isset($widget['chosen_option']['category_path']) && isset($widget['chosen_option']['filter_sku'])) {
                    $product = $this->createProduct($widget);
                    $categoryName = $product->getCategoryIds()[0]['name'];
                    $productSku = $product->getData('sku');
                    $this->data['widget']['preset'][$key]['chosen_option']['category_path'] = $categoryName;
                    $this->data['widget']['preset'][$key]['chosen_option']['filter_sku'] = $productSku;
                }
                if ($widget['widget_type'] == 'Catalog New Products List') {
                    $this->createProduct();
                }
                if ($widget['widget_type'] == 'CMS Static Block') {
                    $block = $this->createBlock($widget);
                    $blockIdentifier = $block->getIdentifier();
                    $this->data['widget']['preset'][$key]['chosen_option']['filter_identifier'] = $blockIdentifier;
                }
            }
        }
    }

    /**
     * Create category.
     *
     * @param array $widget
     * @return Category
     */
    protected function createCategory($widget)
    {
        /** @var Category $category */
        $category = $this->fixtureFactory->createByCode(
            'category',
            ['dataSet' => $widget['chosen_option']['category_path']]
        );
        if (!$category->hasData('id')) {
            $category->persist();
        }

        return $category;
    }

    /**
     * Create product.
     *
     * @param array|null $widget [optional]
     * @return CatalogProductSimple
     */
    protected function createProduct($widget = null)
    {
        $dataSet = $widget === null ? 'default' : $widget['chosen_option']['category_path'];
        /** @var CatalogProductSimple $product */
        $product = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataSet' => $dataSet]);
        if (!$product->hasData('id')) {
            $product->persist();
        }

        return $product;
    }

    /**
     * Create block.
     *
     * @param array $widget
     * @return CmsBlock
     */
    protected function createBlock($widget)
    {
        /** @var CmsBlock $block */
        $block = $this->fixtureFactory->createByCode($widget['chosen_option']['filter_identifier']);
        if (!$block->hasData('block_id')) {
            $block->persist();
        }

        return $block;
    }

    /**
     * Persist attribute options.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string|null $key
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Preset for Widgets.
     *
     * @param string $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'widget_1' => [
                    'widget_type' => 'CMS Page Link',
                    'anchor_text' => 'CMS Page Link anchor_text_%isolation%',
                    'title' => 'CMS Page Link anchor_title_%isolation%',
                    'template' => 'CMS Page Link Block Template',
                    'chosen_option' => [
                        'filter_url_key' => 'home',
                    ],
                ],
            ],
            'all_widgets' => [
                'widget_1' => [
                    'widget_type' => 'CMS Page Link',
                    'anchor_text' => 'CMS Page Link anchor_text_%isolation%',
                    'title' => 'CMS Page Link anchor_title_%isolation%',
                    'template' => 'CMS Page Link Block Template',
                    'chosen_option' => [
                        'filter_url_key' => 'home',
                    ],
                ],
                'widget_2' => [
                    'widget_type' => 'CMS Static Block',
                    'template' => 'CMS Static Block Default Template',
                    'chosen_option' => [
                        'filter_identifier' => 'cmsBlock',
                    ],
                ],
                'widget_3' => [
                    'widget_type' => 'Catalog Category Link',
                    'anchor_text' => 'Catalog Category Link anchor_text_%isolation%',
                    'title' => 'Catalog Category Link anchor_title_%isolation%',
                    'template' => 'Category Link Block Template',
                    'chosen_option' => [
                        'category_path' => 'default_subcategory',
                    ],
                ],
                'widget_4' => [
                    'widget_type' => 'Catalog New Products List',
                    'display_type' => 'All products',
                    'show_pager' => 'Yes',
                    'products_count' => 10,
                    'template' => 'New Products Grid Template',
                    'cache_lifetime' => 86400,
                ],
                'widget_5' => [
                    'widget_type' => 'Catalog Product Link',
                    'anchor_text' => 'Catalog Product Link anchor_text_%isolation%',
                    'title' => 'Catalog Product Link anchor_title_%isolation%',
                    'template' => 'Product Link Block Template',
                    'chosen_option' => [
                        'category_path' => 'product_with_category',
                        'filter_sku' => 'product_with_category',
                    ],
                ],
                'widget_6' => [
                    'widget_type' => 'Recently Compared Products',
                    'page_size' => 10,
                    'template' => 'Compared Products Grid Template',
                ],
                'widget_7' => [
                    'widget_type' => 'Recently Viewed Products',
                    'page_size' => 10,
                    'template' => 'Viewed Products Grid Template',
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
