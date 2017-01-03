<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Create and return Category.
 */
class CategoryIds extends DataSource
{
    /**
     * Fixtures of category
     *
     * @var array
     */
    protected $categories;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        array $params,
        array $data = []
    ) {
        $this->params = $params;

        if (!empty($data['category'])
            && empty($data['dataset'])
            && $data['category'] instanceof Category
        ) {
            /** @var Category $category */
            $category = $data['category'];
            if (!$category->hasData('id')) {
                $category->persist();
            }
            $this->data[] = $category->getName();
            $this->categories[] = $category;
        } elseif (isset($data['dataset'])) {
            $datasets = explode(',', $data['dataset']);
            foreach ($datasets as $dataset) {
                $category = $fixtureFactory->createByCode('category', ['dataset' => trim($dataset)]);
                if (!isset($data['new_category']) || $data['new_category'] !== 'yes') {
                    $category->persist();
                }

                /** @var Category $category */
                $this->data[] = $category->getName();
                $this->categories[] = $category;
            }
        } else {
            foreach ($data as $category) {
                if ($category instanceof Category) {
                    if (!$category->hasData('id')) {
                        $category->persist();
                    }
                    $this->data[] = $category->getName();
                    $this->categories[] = $category;
                }
            }
        }
    }

    /**
     * Return category array
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get id of categories
     *
     * @return array
     */
    public function getIds()
    {
        $ids = [];
        foreach ($this->categories as $category) {
            $ids[] = $category->getId();
        }

        return $ids;
    }
}
