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
    // small prices test case
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
    // simple test case (should skip first quantile)
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
    // test with large values (variance is near to zero)
    array(
        array_merge(array(9659.57), array_fill(0, 231, 9659.58), array(9659.59)),
        10,
        array(
            array(
                'from'  => 9659.57,
                'to'    => 9659.57,
                'count' => 1,
            ),
            array(
                'from'  => 9659.58,
                'to'    => 9659.58,
                'count' => 231,
            ),
            array(
                'from'  => 9659.59,
                'to'    => 9659.59,
                'count' => 1,
            ),
        )
    ),
    // another test with large values (variance is near to zero)
    array(
        array_merge(array(8997.71), array_fill(0, 291, 8997.72), array(8997.73)),
        10,
        array(
            array(
                'from'  => 8997.71,
                'to'    => 8997.71,
                'count' => 1,
            ),
            array(
                'from'  => 8997.72,
                'to'    => 8997.72,
                'count' => 291,
            ),
            array(
                'from'  => 8997.73,
                'to'    => 8997.73,
                'count' => 1,
            ),
        )
    ),
    // simple test
    array(
        array(3336.23, 3336.24, 3336.24),
        2,
        array(
            array(
                'from'  => 3336.23,
                'to'    => 3336.23,
                'count' => 1,
            ),
            array(
                'from'  => 3336.24,
                'to'    => 3336.24,
                'count' => 2,
            ),
        )
    ),
    // simple test
    array(
        array(6323.19, 6323.2, 6323.2, 6323.2),
        2,
        array(
            array(
                'from'  => 6323.19,
                'to'    => 6323.19,
                'count' => 1,
            ),
            array(
                'from'  => 6323.2,
                'to'    => 6323.2,
                'count' => 3,
            ),
        )
    ),
    // simple test
    array(
        array(8732.58, 8732.59, 8732.59, 8732.59),
        2,
        array(
            array(
                'from'  => 8732.58,
                'to'    => 8732.58,
                'count' => 1,
            ),
            array(
                'from'  => 8732.59,
                'to'    => 8732.59,
                'count' => 3,
            ),
        )
    ),
    // simple test
    array(
        array(2623.35, 2623.36, 2623.36),
        2,
        array(
            array(
                'from'  => 2623.35,
                'to'    => 2623.35,
                'count' => 1,
            ),
            array(
                'from'  => 2623.36,
                'to'    => 2623.36,
                'count' => 2,
            ),
        )
    ),
    // simple test
    array(
        array(
            13.5, 41.95, 69.99, 89.99, 99.99, 99.99, 160.99, 161.94, 199.99, 199.99, 199.99,
            239.99, 329.99, 447.98, 550, 599.99, 699.99, 750, 847.97, 1599.99, 2699.99, 4999.95
        ), 4, array(
            array(
                'from'  => 0,
                'to'    => 100,
                'count' => 6,
            ),
            array(
                'from'  => 100,
                'to'    => 200,
                'count' => 5,
            ),
            array(
                'from'  => 200,
                'to'    => 600,
                'count' => 5,
            ),
            array(
                'from'  => 600,
                'to'    => '',
                'count' => 6,
            ),
        )
    ),
    // simple test
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
);


// generate random data
for ($i = 0; $i < 50; ++$i) {
    $randomPrice       = mt_rand(1, 1000000) / 100;
    $randomCount       = mt_rand(2, 300);
    $randomCount1      = $randomCount + 4;
    $randomPrice1      = round(
        $randomPrice  + Mage_Catalog_Model_Layer_Filter_Price_Algorithm::MIN_POSSIBLE_PRICE, 2
    );
    $randomPrice2      = round(
        $randomPrice1 + Mage_Catalog_Model_Layer_Filter_Price_Algorithm::MIN_POSSIBLE_PRICE, 2
    );

    $testCases = array_merge($testCases, array(
        // one product with random price
        array(array($randomPrice), 1, array(array(
            'from'  => $randomPrice,
            'to'    => $randomPrice,
            'count' => 1,
        ))),
        // several products with the same price
        array(array_fill(0, $randomCount, $randomPrice), 1, array(array(
            'from'  => $randomPrice,
            'to'    => $randomPrice,
            'count' => $randomCount,
        ))),
        // one price is less than others
        array(
            array_merge(array($randomPrice), array_fill(
                0,
                $randomCount,
                $randomPrice1
            )),
            null,
            array(
                array(
                    'from'  => $randomPrice,
                    'to'    => $randomPrice,
                    'count' => 1,
                ),
                array(
                    'from'  => $randomPrice1,
                    'to'    => $randomPrice1,
                    'count' => $randomCount,
                ),
            )
        ),
        // one price is bigger than others
        array(
            array_merge(array_fill(
                0,
                $randomCount,
                $randomPrice
            ), array($randomPrice1)),
            null,
            array(
                array(
                    'from'  => $randomPrice,
                    'to'    => $randomPrice,
                    'count' => $randomCount,
                ),
                array(
                    'from'  => $randomPrice1,
                    'to'    => $randomPrice1,
                    'count' => 1,
                ),
            )
        ),
        // one price is less and one is bigger than others
        array(
            array_merge(
                array($randomPrice), array_fill(
                    0,
                    $randomCount1,
                    $randomPrice1
                ), array($randomPrice2)
            ),
            null,
            array(
                array(
                    'from'  => $randomPrice,
                    'to'    => $randomPrice,
                    'count' => 1,
                ),
                array(
                    'from'  => $randomPrice1,
                    'to'    => $randomPrice1,
                    'count' => $randomCount1,
                ),
                array(
                    'from'  => $randomPrice2,
                    'to'    => $randomPrice2,
                    'count' => 1,
                ),
            )
        ),
    ));
}

return $testCases;
