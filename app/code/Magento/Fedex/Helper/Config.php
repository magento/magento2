<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Fedex\Helper;
use Magento\Shipping\Model\ConfigInterface;


/**
 * Configuration data of carrier
 */
class Config extends \Magento\Shipping\Helper\AbstractConfig implements ConfigInterface
{
    /**
     * Get configuration data of carrier
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    function getCodes()
    {
        return [
            'method' => [
                'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => __('Europe First Priority'),
                'FEDEX_1_DAY_FREIGHT' => __('1 Day Freight'),
                'FEDEX_2_DAY_FREIGHT' => __('2 Day Freight'),
                'FEDEX_2_DAY' => __('2 Day'),
                'FEDEX_2_DAY_AM' => __('2 Day AM'),
                'FEDEX_3_DAY_FREIGHT' => __('3 Day Freight'),
                'FEDEX_EXPRESS_SAVER' => __('Express Saver'),
                'FEDEX_GROUND' => __('Ground'),
                'FIRST_OVERNIGHT' => __('First Overnight'),
                'GROUND_HOME_DELIVERY' => __('Home Delivery'),
                'INTERNATIONAL_ECONOMY' => __('International Economy'),
                'INTERNATIONAL_ECONOMY_FREIGHT' => __('Intl Economy Freight'),
                'INTERNATIONAL_FIRST' => __('International First'),
                'INTERNATIONAL_GROUND' => __('International Ground'),
                'INTERNATIONAL_PRIORITY' => __('International Priority'),
                'INTERNATIONAL_PRIORITY_FREIGHT' => __('Intl Priority Freight'),
                'PRIORITY_OVERNIGHT' => __('Priority Overnight'),
                'SMART_POST' => __('Smart Post'),
                'STANDARD_OVERNIGHT' => __('Standard Overnight'),
                'FEDEX_FREIGHT' => __('Freight'),
                'FEDEX_NATIONAL_FREIGHT' => __('National Freight'),
            ],
            'dropoff' => [
                'REGULAR_PICKUP' => __('Regular Pickup'),
                'REQUEST_COURIER' => __('Request Courier'),
                'DROP_BOX' => __('Drop Box'),
                'BUSINESS_SERVICE_CENTER' => __('Business Service Center'),
                'STATION' => __('Station'),
            ],
            'packaging' => [
                'FEDEX_ENVELOPE' => __('FedEx Envelope'),
                'FEDEX_PAK' => __('FedEx Pak'),
                'FEDEX_BOX' => __('FedEx Box'),
                'FEDEX_TUBE' => __('FedEx Tube'),
                'FEDEX_10KG_BOX' => __('FedEx 10kg Box'),
                'FEDEX_25KG_BOX' => __('FedEx 25kg Box'),
                'YOUR_PACKAGING' => __('Your Packaging'),
            ],
            'containers_filter' => [
                [
                    'containers' => ['FEDEX_ENVELOPE', 'FEDEX_PAK'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                'FEDEX_EXPRESS_SAVER',
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                            ],
                        ],
                        'from_us' => [
                            'method' => ['INTERNATIONAL_FIRST', 'INTERNATIONAL_ECONOMY', 'INTERNATIONAL_PRIORITY'],
                        ],
                    ],
                ],
                [
                    'containers' => ['FEDEX_BOX', 'FEDEX_TUBE'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                                'FEDEX_FREIGHT',
                                'FEDEX_1_DAY_FREIGHT',
                                'FEDEX_2_DAY_FREIGHT',
                                'FEDEX_3_DAY_FREIGHT',
                                'FEDEX_NATIONAL_FREIGHT',
                            ],
                        ],
                        'from_us' => [
                            'method' => ['INTERNATIONAL_FIRST', 'INTERNATIONAL_ECONOMY', 'INTERNATIONAL_PRIORITY'],
                        ],
                    ]
                ],
                [
                    'containers' => ['FEDEX_10KG_BOX', 'FEDEX_25KG_BOX'],
                    'filters' => [
                        'within_us' => [],
                        'from_us' => ['method' => ['INTERNATIONAL_PRIORITY']],
                    ]
                ],
                [
                    'containers' => ['YOUR_PACKAGING'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                'FEDEX_GROUND',
                                'GROUND_HOME_DELIVERY',
                                'SMART_POST',
                                'FEDEX_EXPRESS_SAVER',
                                'FEDEX_2_DAY',
                                'FEDEX_2_DAY_AM',
                                'STANDARD_OVERNIGHT',
                                'PRIORITY_OVERNIGHT',
                                'FIRST_OVERNIGHT',
                                'FEDEX_FREIGHT',
                                'FEDEX_1_DAY_FREIGHT',
                                'FEDEX_2_DAY_FREIGHT',
                                'FEDEX_3_DAY_FREIGHT',
                                'FEDEX_NATIONAL_FREIGHT',
                            ],
                        ],
                        'from_us' => [
                            'method' => [
                                'INTERNATIONAL_FIRST',
                                'INTERNATIONAL_ECONOMY',
                                'INTERNATIONAL_PRIORITY',
                                'INTERNATIONAL_GROUND',
                                'FEDEX_FREIGHT',
                                'FEDEX_1_DAY_FREIGHT',
                                'FEDEX_2_DAY_FREIGHT',
                                'FEDEX_3_DAY_FREIGHT',
                                'FEDEX_NATIONAL_FREIGHT',
                                'INTERNATIONAL_ECONOMY_FREIGHT',
                                'INTERNATIONAL_PRIORITY_FREIGHT',
                            ],
                        ],
                    ]
                ],
            ],
            'delivery_confirmation_types' => [
                'NO_SIGNATURE_REQUIRED' => __('Not Required'),
                'ADULT' => __('Adult'),
                'DIRECT' => __('Direct'),
                'INDIRECT' => __('Indirect'),
            ],
            'unit_of_measure' => [
                'LB' => __('Pounds'),
                'KG' => __('Kilograms'),
            ],
        ];
    }
}
