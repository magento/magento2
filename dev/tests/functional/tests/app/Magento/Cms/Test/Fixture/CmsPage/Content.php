<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $params,
        array $data = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $this->data = $data;
        if (isset($data['widget']['dataset']) && isset($this->params['repository'])) {
            $this->data['widget']['dataset'] = $repositoryFactory->get($this->params['repository'])->get(
                $data['widget']['dataset']
            );
            foreach ($this->data['widget']['dataset'] as $key => $widget) {
                if (isset($widget['chosen_option']['category_path'])
                    && !isset($widget['chosen_option']['filter_sku'])
                ) {
                    $category = $this->createCategory($widget);
                    $categoryName = $category->getData('name');
                    $this->data['widget']['dataset'][$key]['chosen_option']['category_path'] = $categoryName;
                }
                if (isset($widget['chosen_option']['category_path']) && isset($widget['chosen_option']['filter_sku'])) {
                    $product = $this->createProduct($widget);
                    $categoryName = $product->getCategoryIds()[0]['name'];
                    $productSku = $product->getData('sku');
                    $this->data['widget']['dataset'][$key]['chosen_option']['category_path'] = $categoryName;
                    $this->data['widget']['dataset'][$key]['chosen_option']['filter_sku'] = $productSku;
                }
                if ($widget['widget_type'] == 'Catalog New Products List') {
                    $this->createProduct();
                }
                if ($widget['widget_type'] == 'CMS Static Block') {
                    $block = $this->createBlock($widget);
                    $blockIdentifier = $block->getIdentifier();
                    $this->data['widget']['dataset'][$key]['chosen_option']['filter_identifier'] = $blockIdentifier;
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
