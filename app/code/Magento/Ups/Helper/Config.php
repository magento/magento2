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
namespace Magento\Ups\Helper;

/**
 * Configuration data of carrier
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
     */
    protected function getCodes()
    {
        return array(
            'action' => array('single' => '3', 'all' => '4'),
            'originShipment' => array(
                // United States Domestic Shipments
                'United States Domestic Shipments' => array(
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
                    '65' => __('UPS Saver')
                ),
                // Shipments Originating in United States
                'Shipments Originating in United States' => array(
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
                    '65' => __('UPS Worldwide Saver')
                ),
                // Shipments Originating in Canada
                'Shipments Originating in Canada' => array(
                    '01' => __('UPS Express'),
                    '02' => __('UPS Expedited'),
                    '07' => __('UPS Worldwide Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '11' => __('UPS Standard'),
                    '12' => __('UPS Three-Day Select'),
                    '14' => __('UPS Express Early A.M.'),
                    '65' => __('UPS Saver')
                ),
                // Shipments Originating in the European Union
                'Shipments Originating in the European Union' => array(
                    '07' => __('UPS Express'),
                    '08' => __('UPS Expedited'),
                    '11' => __('UPS Standard'),
                    '54' => __('UPS Worldwide Express PlusSM'),
                    '65' => __('UPS Saver')
                ),
                // Polish Domestic Shipments
                'Polish Domestic Shipments' => array(
                    '07' => __('UPS Express'),
                    '08' => __('UPS Expedited'),
                    '11' => __('UPS Standard'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '65' => __('UPS Saver'),
                    '82' => __('UPS Today Standard'),
                    '83' => __('UPS Today Dedicated Courrier'),
                    '84' => __('UPS Today Intercity'),
                    '85' => __('UPS Today Express'),
                    '86' => __('UPS Today Express Saver')
                ),
                // Puerto Rico Origin
                'Puerto Rico Origin' => array(
                    '01' => __('UPS Next Day Air'),
                    '02' => __('UPS Second Day Air'),
                    '03' => __('UPS Ground'),
                    '07' => __('UPS Worldwide Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '14' => __('UPS Next Day Air Early A.M.'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '65' => __('UPS Saver')
                ),
                // Shipments Originating in Mexico
                'Shipments Originating in Mexico' => array(
                    '07' => __('UPS Express'),
                    '08' => __('UPS Expedited'),
                    '54' => __('UPS Express Plus'),
                    '65' => __('UPS Saver')
                ),
                // Shipments Originating in Other Countries
                'Shipments Originating in Other Countries' => array(
                    '07' => __('UPS Express'),
                    '08' => __('UPS Worldwide Expedited'),
                    '11' => __('UPS Standard'),
                    '54' => __('UPS Worldwide Express Plus'),
                    '65' => __('UPS Saver')
                )
            ),
            'method' => array(
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
                'XPD' => __('Worldwide Expedited')
            ),
            'pickup' => array(
                'RDP' => array("label" => 'Regular Daily Pickup', "code" => "01"),
                'OCA' => array("label" => 'On Call Air', "code" => "07"),
                'OTP' => array("label" => 'One Time Pickup', "code" => "06"),
                'LC' => array("label" => 'Letter Center', "code" => "19"),
                'CC' => array("label" => 'Customer Counter', "code" => "03")
            ),
            'container' => array(
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
                'LEB' => '2c'
            ),
            'container_description' => array(
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
                'LEB' => __('Large Express Box')
            ),
            'dest_type' => array('RES' => '01', 'COM' => '02'),
            'dest_type_description' => array('RES' => __('Residential'), 'COM' => __('Commercial')),
            'unit_of_measure' => array('LBS' => __('Pounds'), 'KGS' => __('Kilograms')),
            'containers_filter' => array(
                array(
                    'containers' => array('00'), // Customer Packaging
                    'filters' => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '12', // 3 Day Select
                                '59', // 2nd Day Air AM
                                '03', // Ground
                                '14', // Next Day Air Early AM
                                '02' // 2nd Day Air
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                                '11' // Standard
                            )
                        )
                    )
                ),
                // Small Express Box, Medium Express Box, Large Express Box, UPS Tube
                array(
                    'containers' => array('2a', '2b', '2c', '03'),
                    'filters' => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13' // Next Day Air Saver
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65' // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('24', '25'), // UPS Worldwide 25 kilo, UPS Worldwide 10 kilo
                    'filters' => array(
                        'within_us' => array('method' => array()),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65' // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('01', '04'), // UPS Letter, UPS PAK
                    'filters' => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13' // Next Day Air Saver
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65' // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('04'), // UPS PAK
                    'filters' => array(
                        'within_us' => array('method' => array()),
                        'from_us' => array('method' => array('08')) // Worldwide Expedited
                    )
                )
            )
        );
    }
}
