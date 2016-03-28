<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Fixture\CmsPage;

use Magento\Mtf\Fixture\DataSource;
use Magento\Cms\Test\Fixture\CmsBlock;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Repository\RepositoryFactory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Prepare content for cms page.
 */
class Content extends DataSource
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Repository factory.
     *
     * @var RepositoryFactory
     */
    protected $repositoryFactory;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $params,
        array $data = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->repositoryFactory = $repositoryFactory;
        $this->params = $params;
        $this->data = $data;
        $this->prepareSourceData();
    }

    /**
     * Prepare source data.
     *
     * @return void
     */
    protected function prepareSourceData()
    {
        if (isset($this->data['widget']['dataset']) && isset($this->params['repository'])) {
            $this->data['widget']['dataset'] = $this->repositoryFactory->get($this->params['repository'])->get(
                $this->data['widget']['dataset']
            );
            $this->data = array_merge($this->data, $this->prepareWidgetData($this->data['widget']));
        }
    }

    /**
     * Prepare widget data for the source.
     *
     * @param array $widgets
     * @return array
     */
    protected function prepareWidgetData(array $widgets)
    {
        $data = [];
        foreach ($widgets['dataset'] as $key => $widget) {
            if (isset($widget['chosen_option']['category_path'])
                && !isset($widget['chosen_option']['filter_sku'])
            ) {
                $category = $this->createCategory($widget);
                $categoryName = $category->getData('name');
                $data['widget']['dataset'][$key]['chosen_option']['category_path'] = $categoryName;
            }
            if (isset($widget['chosen_option']['category_path']) && isset($widget['chosen_option']['filter_sku'])) {
                $product = $this->createProduct($widget);
                $categoryName = $product->getCategoryIds()[0]['name'];
                $productSku = $product->getData('sku');
                $data['widget']['dataset'][$key]['chosen_option']['category_path'] = $categoryName;
                $data['widget']['dataset'][$key]['chosen_option']['filter_sku'] = $productSku;
            }
            if ($widget['widget_type'] == 'Catalog New Products List') {
                $this->createProduct();
            }
            if ($widget['widget_type'] == 'CMS Static Block') {
                $block = $this->createBlock($widget);
                $blockIdentifier = $block->getIdentifier();
                $data['widget']['dataset'][$key]['chosen_option']['filter_identifier'] = $blockIdentifier;
            }
        }

        return $data;
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
            ['dataset' => $widget['chosen_option']['category_path']]
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
        $dataset = $widget === null ? 'default' : $widget['chosen_option']['category_path'];
        /** @var CatalogProductSimple $product */
        $product = $this->fixtureFactory->createByCode('catalogProductSimple', ['dataset' => $dataset]);
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
}
