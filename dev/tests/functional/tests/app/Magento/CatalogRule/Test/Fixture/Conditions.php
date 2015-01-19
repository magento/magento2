<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Fixture;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Fixture\CatalogProductSimple\CategoryIds;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Conditions
 *
 * Data keys:
 *  - preset (Conditions options preset name)
 *
 */
class Conditions implements FixtureInterface
{
    /**
     * @var CatalogProductSimple
     */
    protected $product;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['product'])) {
            list($fixture, $dataSet) = explode('::', $data['product']);
            $this->product = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
            $this->product->persist();

            /** @var CategoryIds $sourceCategories */
            $sourceCategories = $this->product->getDataFieldConfig('category_ids')['source'];
            $this->data = $sourceCategories->getIds()[0];
        }
    }

    /**
     * Persist conditions
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
     * @param $key [optional]
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
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Get product for verification
     *
     * @return \Magento\Catalog\Test\Fixture\CatalogProductSimple
     */
    public function getProduct()
    {
        return $this->product;
    }
}
