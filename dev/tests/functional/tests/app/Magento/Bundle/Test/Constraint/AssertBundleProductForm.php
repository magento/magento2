<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductForm;

/**
 * Assert bundle product form.
 */
class AssertBundleProductForm extends AssertProductForm
{
    /**
     * Skipped fields for verify data.
     *
     * @var array
     */
    protected $skippedFields = ['frontend_type'];

    /**
     * Formatting options for array values.
     *
     * @var array
     */
    protected $specialArray = [
        'special_from_date' => [
            'type' => 'date',
        ],
        'special_to_date' => [
            'type' => 'date',
        ],
    ];

    /**
     * Prepares fixture data for comparison.
     *
     * @param array $data
     * @param array $sortFields [optional]
     * @return array
     */
    protected function prepareFixtureData(array $data, array $sortFields = [])
    {
        $data['bundle_selections'] = $this->prepareBundleOptions(
            $data['bundle_selections']['bundle_options']
        );

        return parent::prepareFixtureData($data, $sortFields);
    }

    /**
     * Prepare Bundle Options array from dataset.
     *
     * @param array $bundleSelections
     * @return array
     */
    protected function prepareBundleOptions(array $bundleSelections)
    {
        foreach ($bundleSelections as &$item) {
            foreach ($item['assigned_products'] as &$selection) {
                $selection['data']['getProductName'] = $selection['search_data']['name'];
                $selection = $selection['data'];
            }
        }

        return $bundleSelections;
    }
}
