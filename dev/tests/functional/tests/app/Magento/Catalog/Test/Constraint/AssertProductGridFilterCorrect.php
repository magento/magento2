<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Block\Adminhtml\Product\Grid;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Assert to check that name of product in grid is changing when store filter changed
 *
 */
class AssertProductGridFilterCorrect extends AbstractConstraint
{
    /**
     * Constant to apply filter for all store views
     */
    const ALL_STORE_VIEWS_FILTER = 'All Store Views';

    /**
     * Assert that product is present in products grid and products filter is cached correct.
     *
     * @param FixtureInterface $initialProduct
     * @param CatalogProductIndex $productIndex
     * @param array $productNames
     * @param array $stores
     */
    public function processAssert(
        FixtureInterface $initialProduct,
        CatalogProductIndex $productIndex,
        array $productNames,
        array $stores
    ) {
        $productSku = $initialProduct->getSku();
        //open products grid
        $productIndex->open();
        /** @var Grid $productGrid */
        $productGrid = $productIndex->getProductGrid();
        $productGrid->resetFilter();

        //form data for filters
        $dataForFilterAllStores = [
            [
                'name' => $initialProduct->getName(),
                'store_view' => self::ALL_STORE_VIEWS_FILTER
            ]
        ];

        $dataForFilterCustomStores = [];
        if (!empty($productNames) && !empty($stores)) {
            foreach ($stores as $store) {
                $storeName = $store->getName();
                $storeId = $store->getStoreId();
                if (isset($productNames[$storeId])) {
                    $dataForFilterCustomStores[] = [
                        'name' => $productNames[$storeId],
                        'store_view' => $storeName
                    ];
                }
            }
        }

        $dataForFilters = array_merge($dataForFilterCustomStores, $dataForFilterAllStores, $dataForFilterCustomStores);

        //apply filters and compare results
        foreach ($dataForFilters as $filterData) {
            if (!empty($filterData)) {
                $filter = [
                    'store_id' => $filterData['store_view'],
                    'sku' => $productSku,
                ];
                $productGrid->resetFilter();
                $productGrid->search($filter);
                $res = $productGrid->isRowVisible(['name' => $filterData['name']], false, true);

                \PHPUnit_Framework_Assert::assertTrue(
                    $res,
                    'Product \'' . $initialProduct->getName() . '\' is absent in Products grid.'
                );

            } else {
                $productGrid->resetFilter();
            }
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Products grid filter is cached correct';
    }
}
