<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogCategory;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class ParentId
 * Prepare parent category
 */
class ParentId implements FixtureInterface
{
    /**
     * Return category
     *
     * @var CatalogCategory
     */
    protected $parentCategory = null;

    /**
     * Fixture params
     *
     * @var array
     */
    protected $params;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array|int $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet']) && $data['dataSet'] !== '-') {
            $this->parentCategory = $fixtureFactory->createByCode('catalogCategory', ['dataSet' => $data['dataSet']]);
            if (!$this->parentCategory->hasData('id')) {
                $this->parentCategory->persist();
            }
            $this->data = $this->parentCategory->getId();
        } else {
            $this->data = $data;
        }
    }

    /**
     * Persist attribute options
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
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
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Return entity
     *
     * @return CatalogCategory
     */
    public function getParentCategory()
    {
        return $this->parentCategory;
    }
}
