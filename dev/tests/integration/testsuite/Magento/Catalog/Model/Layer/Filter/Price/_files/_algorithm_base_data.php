<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test cases for pricesSegmentationDataProvider
 */
$testCases = [
    // no products, no prices
    [[], [], []],
    // small prices
    [
        range(0.01, 0.08, 0.01),
        range(1, 8, 1),
        [['from' => 0, 'to' => 0.05, 'count' => 4], ['from' => 0.05, 'to' => '', 'count' => 4]]
    ],
    // zero price test
    [
        [0, 0.71, 0.89],
        range(9, 11, 1),
        [['from' => 0, 'to' => 0, 'count' => 1], ['from' => 0.5, 'to' => '', 'count' => 2]]
    ],
    // first quantile should be skipped
    [
        [
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.01,
            0.03,
            0.05,
            0.05,
            0.06,
            0.07,
            0.07,
            0.08,
            0.08,
            0.09,
            0.15,
        ],
        range(12, 32, 1),
        [['from' => 0, 'to' => 0.05, 'count' => 12], ['from' => 0.05, 'to' => '', 'count' => 9]]
    ],
    // test if best rounding factor is used
    [
        [10.19, 10.2, 10.2, 10.2, 10.21],
        range(33, 37, 1),
        [['from' => 10.19, 'to' => 10.19, 'count' => 1], ['from' => 10.2, 'to' => '', 'count' => 4]]
    ],
    // quantiles interception
    [
        [
            5.99,
            5.99,
            7.99,
            8.99,
            8.99,
            9.99,
            9.99,
            9.99,
            9.99,
            9.99,
            14.6,
            15.99,
            16,
            16.99,
            17,
            17.5,
            18.99,
            19,
            20.99,
            24.99,
        ],
        range(38, 57, 1),
        [
            ['from' => 0, 'to' => 9, 'count' => 5],
            ['from' => 9.99, 'to' => 9.99, 'count' => 5],
            ['from' => 10, 'to' => '', 'count' => 10]
        ]
    ],
    // test if best rounding factor is used
    [
        [10.18, 10.19, 10.19, 10.19, 10.2],
        range(58, 62, 1),
        [['from' => 0, 'to' => 10.2, 'count' => 4], ['from' => 10.2, 'to' => 10.2, 'count' => 1]]
    ],
    // test many equal values
    [
        array_merge([10.57], array_fill(0, 20, 10.58), [10.59]),
        range(63, 84, 1),
        [
            ['from' => 10.57, 'to' => 10.57, 'count' => 1],
            ['from' => 10.58, 'to' => 10.58, 'count' => 20],
            ['from' => 10.59, 'to' => 10.59, 'count' => 1]
        ]
    ],
    // test preventing low count in interval and rounding factor to have lower priority
    [
        [
            0.01,
            0.01,
            0.01,
            0.02,
            0.02,
            0.03,
            0.03,
            0.04,
            0.04,
            0.04,
            0.05,
            0.05,
            0.05,
            0.06,
            0.06,
            0.06,
            0.06,
            0.07,
            0.07,
            0.08,
            0.08,
            2.99,
            5.99,
            5.99,
            5.99,
            5.99,
            5.99,
            5.99,
            5.99,
            5.99,
            5.99,
            13.50,
            15.99,
            41.95,
            69.99,
            89.99,
            99.99,
            99.99,
            160.99,
            161.94,
            199.99,
            199.99,
            199.99,
            239.99,
            329.99,
            447.98,
            550.00,
            599.99,
            699.99,
            750.00,
            847.97,
            1599.99,
            2699.99,
            4999.95,
        ],
        range(85, 148, 1),
        [
            ['from' => 0, 'to' => 0.05, 'count' => 10],
            // this is important, that not 0.06 is used to prevent low count in interval
            ['from' => 0.05, 'to' => 0.07, 'count' => 7],
            ['from' => 0.07, 'to' => 5, 'count' => 5],
            ['from' => 5.99, 'to' => 5.99, 'count' => 9],
            ['from' => 10, 'to' => 100, 'count' => 7],
            ['from' => 100, 'to' => 500, 'count' => 8],
            ['from' => 500, 'to' => '', 'count' => 8]
        ]
    ],
];

return $testCases;
