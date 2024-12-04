<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ups\Helper;

/**
 * Configuration data of carrier
 *
 * @api
 * @since 100.0.2
 */
class Config
{
    /**
     * Get configuration data of carrier
     *
     * @param string $type
     * @param string $code
     * @return array|string|false
     */
    public function getCode($type, $code = '')
    {
        $codes = $this->getCodes();
        if (!isset($codes[$type])) {
            return false;
        } elseif ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Get configuration data of carrier
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getCodes()
    {
        return [
            'action' => ['single' => '3', 'all' => '4'],
            'originShipment' => [
                // United States Domestic Shipments
                'United States Domestic Shipments' => [
                    '01' => __('UPS Next Day Air'),
                    '02' => __('UPS Second Day Air'),
                    '03' => __('UPS Ground'),
                    '07' => __('UPS Worldwide Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '11' => __('UPS Standard'),
                    '12' => __('UPS Three-Day Select'),
                    '13' => __('UPS Next Day Air Saver'),
                    '14' => __('UPS Next Day Air Early A.M.'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '59' => __('UPS Second Day Air A.M.'),
                    '65' => __('UPS Saver'),
                ],
                // Shipments Originating in United States
                'Shipments Originating in United States' => [
                    '01' => __('UPS Next Day Air'),
                    '02' => __('UPS Second Day Air'),
                    '03' => __('UPS Ground'),
                    '07' => __('UPS Worldwide Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '11' => __('UPS Standard'),
                    '12' => __('UPS Three-Day Select'),
                    '14' => __('UPS Next Day Air Early A.M.'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '59' => __('UPS Second Day Air A.M.'),
                    '65' => __('UPS Worldwide Saver'),
                ],
                // Shipments Originating in Canada
                'Shipments Originating in Canada' => [
                    '01' => __('UPS Express'),
                    '02' => __('UPS Expedited'),
                    '07' => __('UPS Worldwide Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '11' => __('UPS Standard'),
                    '12' => __('UPS Three-Day Select'),
                    '14' => __('UPS Express Early A.M.'),
                    '65' => __('UPS Saver'),
                ],
                // Shipments Originating in the European Union
                'Shipments Originating in the European Union' => [
                    '07' => __('UPS Express'),
                    '08' => __('UPS Expedited'),
                    '11' => __('UPS Standard'),
                    '54' => __('UPS Worldwide Express PlusSM'),
                    '65' => __('UPS Saver'),
                ],
                // Polish Domestic Shipments
                'Polish Domestic Shipments' => [
                    '07' => __('UPS Express'),
                    '08' => __('UPS Expedited'),
                    '11' => __('UPS Standard'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '65' => __('UPS Saver'),
                    '82' => __('UPS Today Standard'),
                    '83' => __('UPS Today Dedicated Courrier'),
                    '84' => __('UPS Today Intercity'),
                    '85' => __('UPS Today Express'),
                    '86' => __('UPS Today Express Saver'),
                ],
                // Puerto Rico Origin
                'Puerto Rico Origin' => [
                    '01' => __('UPS Next Day Air'),
                    '02' => __('UPS Second Day Air'),
                    '03' => __('UPS Ground'),
                    '07' => __('UPS Worldwide Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '12' => __('UPS Three-Day Select'),
                    '14' => __('UPS Next Day Air Early A.M.'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '65' => __('UPS Saver'),
                ],
                // Shipments Originating in Mexico
                'Shipments Originating in Mexico' => [
                    '01' => __('UPS Next Day Air'),
                    '02' => __('UPS Second Day Air'),
                    '03' => __('UPS Ground'),
                    '07' => __('UPS Express'),
                    '08' => __('UPS Expedited'),
                    '11' => __('UPS Standard'),
                    '12' => __('UPS Three-Day Select'),
                    '13' => __('UPS Next Day Air Saver'),
                    '14' => __('UPS Next Day Air Early A.M.'),
                    '54' => __('UPS Express Plus'),
                    '59' => __('UPS Second Day Air A.M.'),
                    '65' => __('UPS Saver'),
                ],
                // Shipments Originating in Other Countries
                'Shipments Originating in Other Countries' => [
                    '07' => __('UPS Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '11' => __('UPS Standard'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '65' => __('UPS Saver'),
                ],
            ],
            'method' => [
                '1DM' => __('Next Day Air Early AM'),
                '1DML' => __('Next Day Air Early AM Letter'),
                '1DA' => __('Next Day Air'),
                '1DAL' => __('Next Day Air Letter'),
                '1DAPI' => __('Next Day Air Intra (Puerto Rico)'),
                '1DP' => __('Next Day Air Saver'),
                '1DPL' => __('Next Day Air Saver Letter'),
                '2DM' => __('2nd Day Air AM'),
                '2DML' => __('2nd Day Air AM Letter'),
                '2DA' => __('2nd Day Air'),
                '2DAL' => __('2nd Day Air Letter'),
                '3DS' => __('3 Day Select'),
                'GND' => __('Ground'),
                'GNDCOM' => __('Ground Commercial'),
                'GNDRES' => __('Ground Residential'),
                'STD' => __('Canada Standard'),
                'XPR' => __('Worldwide Express'),
                'WXS' => __('Worldwide Express Saver'),
                'XPRL' => __('Worldwide Express Letter'),
                'XDM' => __('Worldwide Express Plus'),
                'XDML' => __('Worldwide Express Plus Letter'),
                'XPD' => __('Worldwide Expedited'),
            ],
            'pickup' => [
                'RDP' => ["label" => 'Regular Daily Pickup', "code" => "01"],
                'OCA' => ["label" => 'On Call Air', "code" => "07"],
                'OTP' => ["label" => 'One Time Pickup', "code" => "06"],
                'LC' => ["label" => 'Letter Center', "code" => "19"],
                'CC' => ["label" => 'Customer Counter', "code" => "03"],
            ],
            'container' => [
                'CP' => '00',
                'ULE' => '01',
                'CSP' => '02',
                'UT' => '03',
                'PAK' => '04',
                'UEB' => '21',
                'UW25' => '24',
                'UW10' => '25',
                'PLT' => '30',
                'SEB' => '2a',
                'MEB' => '2b',
                'LEB' => '2c',
            ],
            'container_description' => [
                'CP' => __('Customer Packaging'),
                'ULE' => __('UPS Letter Envelope'),
                'CSP' => __('Customer Supplied Package'),
                'UT' => __('UPS Tube'),
                'PAK' => __('PAK'),
                'UEB' => __('UPS Express Box'),
                'UW25' => __('UPS Worldwide 25 kilo'),
                'UW10' => __('UPS Worldwide 10 kilo'),
                'PLT' => __('Pallet'),
                'SEB' => __('Small Express Box'),
                'MEB' => __('Medium Express Box'),
                'LEB' => __('Large Express Box'),
            ],
            'dest_type' => ['RES' => '01', 'COM' => '02'],
            'dest_type_description' => ['RES' => __('Residential'), 'COM' => __('Commercial')],
            'unit_of_measure' => ['LBS' => __('Pounds'), 'KGS' => __('Kilograms')],
            'containers_filter' => [
                [
                    'containers' => ['00'], // Customer Packaging
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '12', // 3 Day Select
                                '59', // 2nd Day Air AM
                                '03', // Ground
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                            ],
                        ],
                        'from_us' => [
                            'method' => [
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                                '11', // Standard
                            ],
                        ],
                    ],
                ],
                // Small Express Box, Medium Express Box, Large Express Box, UPS Tube
                [
                    'containers' => ['2a', '2b', '2c', '03'],
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13', // Next Day Air Saver
                            ],
                        ],
                        'from_us' => [
                            'method' => [
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                            ],
                        ],
                    ]
                ],
                [
                    'containers' => ['24', '25'], // UPS Worldwide 25 kilo, UPS Worldwide 10 kilo
                    'filters' => [
                        'within_us' => ['method' => []],
                        'from_us' => [
                            'method' => [
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65', // Worldwide Saver
                            ],
                        ],
                    ]
                ],
                [
                    'containers' => ['01', '04'], // UPS Letter, UPS PAK
                    'filters' => [
                        'within_us' => [
                            'method' => [
                                '01', // Next Day Air
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13', // Next Day Air Saver
                            ],
                        ],
                        'from_us' => [
                            'method' => [
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65', // Worldwide Saver
                            ],
                        ],
                    ]
                ],
                [
                    'containers' => ['04'], // UPS PAK
                    'filters' => [
                        'within_us' => ['method' => []],
                        'from_us' => ['method' => ['08']], // Worldwide Expedited
                    ]
                ],
            ]
        ];
    }
}
