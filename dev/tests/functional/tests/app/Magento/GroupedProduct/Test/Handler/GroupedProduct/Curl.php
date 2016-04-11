<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Handler\GroupedProduct;

use Magento\Catalog\Test\Handler\CatalogProductSimple\Curl as AbstractCurl;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class Curl
 * Create new grouped product via curl
 */
class Curl extends AbstractCurl implements GroupedProductInterface
{
    /**
     * Prepare POST data for creating product request
     *
     * @param FixtureInterface $fixture
     * @param string|null $prefix [optional]
     * @return array
     */
    protected function prepareData(FixtureInterface $fixture, $prefix = null)
    {
        $data = parent::prepareData($fixture, null);

        $assignedProducts = [];
        if (!empty($data['associated'])) {
            $assignedProducts = $data['associated']['assigned_products'];
            unset($data['associated']);
        }

        $data = $prefix ? [$prefix => $data] : $data;
        foreach ($assignedProducts as $item) {
            $data['links']['associated'][$item['id']] = $item;
        }

        return $data;
    }

    /**
     * Preparation of stock data.
     *
     * @param array $fields
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function prepareStockData(array $fields)
    {
        $fields = parent::prepareStockData($fields);
        if (
            isset($fields['quantity_and_stock_status']['is_in_stock'])
            && $fields['quantity_and_stock_status']['is_in_stock']
        ) {
            $fields['quantity_and_stock_status']['use_config_manage_stock'] = 1;
        }
        return $fields;
    }
}
