<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Handler\GroupedProduct;

use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as AbstractCurl;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Create new grouped product via curl.
 */
class Curl extends AbstractCurl implements GroupedProductInterface
{
    /**
     * Prepare POST data for creating product request.
     *
     * @param FixtureInterface $fixture
     * @return array
     */
    public function prepareData(FixtureInterface $fixture)
    {
        $data = parent::prepareData($fixture);

        $assignedProducts = [];
        if (!empty($data['product']['associated'])) {
            $assignedProducts = $data['product']['associated']['assigned_products'];
            unset($data['product']['associated']);
        }

        foreach ($assignedProducts as $item) {
            $data['links']['associated'][$item['id']] = $item;
        }

        return $data;
    }
}
