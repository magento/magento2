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
 * @category    Magento
 * @package     Mage_Catalog
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test cases for pricesSegmentationDataProvider
 */
$testCases = array(
    // no products, no prices
    array(array(), 1, array()),
    // small prices
    array(range(0.01, 0.08, 0.01), 2, array(
        array(
            'from'  => 0,
            'to'    => 0.05,
            'count' => 4,
        ),
        array(
            'from'  => 0.05,
            'to'    => '',
            'count' => 4,
        ),
    )),
    // zero price test
    array(array(0, 0.71, 0.89), 2, array(
        array(
            'from'  => 0,
            'to'    => 0,
            'count' => 1,
        ),
        array(
            'from'  => 0.5,
            'to'    => '',
            'count' => 2,
        ),
    )),
    // first quantile should be skipped
    array(
        array(
            0.01, 0.01, 0.01, 0.01, 0.01, 0.01, 0.01, 0.01, 0.01, 0.01,
            0.01, 0.03, 0.05, 0.05, 0.06, 0.07, 0.07, 0.08, 0.08, 0.09, 0.15
        ), 3, array(
            array(
                'from'  => 0,
                'to'    => 0.05,
                'count' => 12,
            ),
            array(
                'from'  => 0.05,
                'to'    => '',
                'count' => 9,
            ),
        )
    ),
    // test if best rounding factor is used
    array(
        array(10.19, 10.2, 10.2, 10.2, 10.21),
        2,
        array(
            array(
                'from'  => 10.19,
                'to'    => 10.19,
                'count' => 1,
            ),
            array(
                'from'  => 10.2,
                'to'    => '',
                'count' => 4,
            ),
        )
    ),
    // quantiles interception
    array(
        array(
            5.99, 5.99, 7.99, 8.99, 8.99, 9.99, 9.99, 9.99, 9.99, 9.99,
            14.6, 15.99, 16, 16.99, 17, 17.5, 18.99, 19, 20.99, 24.99
        ), 3, array(
            array(
                'from'  => 0,
                'to'    => 9,
                'count' => 5,
            ),
            array(
                'from'  => 9.99,
                'to'    => 9.99,
                'count' => 5,
            ),
            array(
                'from'  => 10,
                'to'    => '',
                'count' => 10,
            ),
        )
    ),
    // test if best rounding factor is used
    array(
        array(10.18, 10.19, 10.19, 10.19, 10.2),
        2,
        array(
            array(
                'from'  => 0,
                'to'    => 10.2,
                'count' => 4,
            ),
            array(
                'from'  => 10.2,
                'to'    => 10.2,
                'count' => 1,
            ),
        )
    ),
    // test many equal values
    array(
        array_merge(array(10.57), array_fill(0, 20, 10.58), array(10.59)),
        6,
        array(
            array(
                'from'  => 10.57,
                'to'    => 10.57,
                'count' => 1,
            ),
            array(
                'from'  => 10.58,
                'to'    => 10.58,
                'count' => 20,
            ),
            array(
                'from'  => 10.59,
                'to'    => 10.59,
                'count' => 1,
            ),
        )
    ),
    // test preventing low count in interval and rounding factor to have lower priority
    array(
        array(
            0.01, 0.01, 0.01, 0.02, 0.02, 0.03, 0.03, 0.04, 0.04, 0.04,
            0.05, 0.05, 0.05, 0.06, 0.06, 0.06, 0.06, 0.07, 0.07, 0.08, 0.08,
            2.99, 5.99, 5.99, 5.99, 5.99, 5.99, 5.99, 5.99, 5.99, 5.99, 13.50,
            15.99, 41.95, 69.99, 89.99, 99.99, 99.99, 160.99, 161.94,
            199.99, 199.99, 199.99, 239.99, 329.99, 447.98, 550.00, 599.99,
            699.99, 750.00, 847.97, 1599.99, 2699.99, 4999.95
        ), 7, array(
            array(
                'from'  => 0,
                'to'    => 0.05,
                'count' => 10,
            ),
            // this is important, that not 0.06 is used to prevent low count in interval
            array(
                'from'  => 0.05,
                'to'    => 0.07,
                'count' => 7,
            ),
            array(
                'from'  => 0.07,
                'to'    => 5,
                'count' => 5,
            ),
            array(
                'from'  => 5.99,
                'to'    => 5.99,
                'count' => 9,
            ),
            array(
                'from'  => 10,
                'to'    => 100,
                'count' => 7,
            ),
            array(
                'from'  => 100,
                'to'    => 500,
                'count' => 8,
            ),
            array(
                'from'  => 500,
                'to'    => '',
                'count' => 8,
            ),
        )
    ),
    // large numbers test
    array(array(100000, 400000, 600000, 900000), 2, array(
        array(
            'from'  => 0,
            'to'    => 500000,
            'count' => 2,
        ),
        array(
            'from'  => 500000,
            'to'    => '',
            'count' => 2,
        ),
    )),
);

return $testCases;
