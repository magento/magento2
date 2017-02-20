<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Fixture\StoreGroup;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\Category;

/**
 * Prepare CategoryId for Store Group.
 */
class CategoryId extends DataSource
{
    /**
     * Category fixture.
     *
     * @var Category
     */
    protected $category;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['fixture']) || isset($data['category'])) {
            $this->category = isset($data['fixture']) ? $data['fixture'] : $data['category'];
            $this->data = $this->category->getName();
        } elseif (isset($data['dataset'])) {
            $category = $fixtureFactory->createByCode('category', ['dataset' => $data['dataset']]);
            /** @var Category $category */
            if (!$category->getId()) {
                $category->persist();
            }
            $this->category = $category;
            $this->data = $category->getName();
        }
    }

    /**
     * Return Category fixture.
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }
}
