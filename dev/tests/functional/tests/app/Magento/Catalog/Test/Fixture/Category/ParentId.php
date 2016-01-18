<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Category;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Prepare parent category.
 * Sets parent_id data as [['id' => 'N' ], ['category' => Category ]]
 */
class ParentId extends DataSource
{
    /**
     * Return category.
     *
     * @var Category
     */
    protected $parentCategory = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|int $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if (isset($data['dataset']) && $data['dataset'] !== '-') {
            $this->parentCategory = $fixtureFactory->createByCode('category', ['dataset' => $data['dataset']]);
            if (!$this->parentCategory->hasData('id')) {
                $this->parentCategory->persist();
            }
            $this->data['id'] = $this->parentCategory->getId();
            $this->data['category'] = $this->parentCategory;
        } else {
            $this->data = $data;
        }
    }

    /**
     * Return entity.
     *
     * @return Category
     */
    public function getParentCategory()
    {
        return $this->parentCategory;
    }
}
