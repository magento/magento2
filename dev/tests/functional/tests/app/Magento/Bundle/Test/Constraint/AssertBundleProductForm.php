<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductForm;

/**
 * Class AssertBundleProductForm
 */
class AssertBundleProductForm extends AssertProductForm
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Formatting options for array values
     *
     * @var array
     */
    protected $specialArray = [
        'special_from_date' => [
            'type' => 'date'
        ],
        'special_to_date' => [
            'type' => 'date'
        ]
    ];

    /**
     * Prepares fixture data for comparison
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
     * Prepare Bundle Options array from preset
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
