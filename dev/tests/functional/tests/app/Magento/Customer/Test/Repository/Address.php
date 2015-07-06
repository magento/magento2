<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Repository;

use Magento\Mtf\Repository\AbstractRepository;

/**
 * Class Address Repository
 * Customer addresses
 *
 */
class Address extends AbstractRepository
{
    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];

        $this->_data['US_address_1'] = $this->_getUS1();
        $this->_data['US_address_2'] = $this->_getUS2();
        $this->_data['address_UK'] = $this->getAddressUK();
        $this->_data['address_UK_2'] = $this->getAddressUK2();
        $this->_data['address_UK_with_VAT'] = $this->getAddressUKWithVAT($this->_data['address_UK']);
        $this->_data['address_DE'] = $this->getAddressDE();
        $this->_data['address_data_US_1'] = $this->_getDataUS1();
    }

    protected function _getUS1()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'John',
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                    ],
                    'email' => [
                        'value' => 'John.Doe%isolation%@example.com',
                    ],
                    'company' => [
                        'value' => 'Magento %isolation%',
                    ],
                    'street' => [
                        'value' => '6161 West Centinela Avenue',
                    ],
                    'city' => [
                        'value' => 'Culver City',
                    ],
                    'region_id' => [
                        'value' => 'California',
                        'input' => 'select',
                    ],
                    'postcode' => [
                        'value' => '90230',
                    ],
                    'country_id' => [
                        'value' => 'United States',
                        'input' => 'select',
                    ],
                    'telephone' => [
                        'value' => '555-55-555-55',
                    ],
                ],
            ]
        ];
    }

    protected function _getBackendUS1()
    {
        return [
            'data' => [
                'fields' => [
                    'save_in_address_book' => [
                        'value' => 'Yes',
                        'input' => 'checkbox',
                    ],
                ],
            ]
        ];
    }

    protected function _getUS2()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'Billy',
                    ],
                    'lastname' => [
                        'value' => 'Holiday',
                    ],
                    'email' => [
                        'value' => 'b.holliday@example.net',
                    ],
                    'company' => [
                        'value' => 'Magento %isolation%',
                    ],
                    'street' => [
                        'value' => '727 5th Ave',
                    ],
                    'city' => [
                        'value' => 'New York',
                    ],
                    'region_id' => [
                        'value' => 'New York',
                        'input' => 'select',
                    ],
                    'postcode' => [
                        'value' => '10022',
                    ],
                    'country_id' => [
                        'value' => 'United States',
                        'input' => 'select',
                    ],
                    'telephone' => [
                        'value' => '777-77-77-77',
                    ],
                ],
            ]
        ];
    }

    protected function _getDataUS1()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'John',
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                    ],
                    'company' => [
                        'value' => 'Magento %isolation%',
                    ],
                    'street' => [
                        'value' => '6161 West Centinela Avenue',
                    ],
                    'country_id' => [
                        'value' => 'United States',
                        'input' => 'select',
                    ],
                    'region_id' => [
                        'value' => 'California',
                        'input' => 'select',
                        'selector' => '#region_id',
                    ],
                    'city' => [
                        'value' => 'Culver City',
                    ],
                    'postcode' => [
                        'value' => '90230',
                    ],
                    'telephone' => [
                        'value' => '555-55-555-55',
                    ],
                ],
            ]
        ];
    }

    /**
     * Get address for UK
     *
     * @return array
     */
    protected function getAddressUK()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'Jane',
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                    ],
                    'telephone' => [
                        'value' => '444-44-444-44',
                    ],
                    'street[]' => [
                        'value' => '172, Westminster Bridge Rd',
                    ],
                    'country_id' => [
                        'value' => 'United Kingdom',
                        'input_value' => 'GB',
                        'input' => 'select',
                        'selector' => '#country',
                    ],
                    'region' => [
                        'value' => 'London',
                    ],
                    'city' => [
                        'value' => 'London',
                    ],
                    'postcode' => [
                        'value' => 'SE1 7RW',
                        'selector' => '#zip',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get second address for UK
     *
     * @return array
     */
    protected function getAddressUK2()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'Jane',
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                    ],
                    'company' => [
                        'value' => 'Magento %isolation%',
                    ],
                    'telephone' => [
                        'value' => '444-44-444-44',
                    ],
                    'street' => [
                        'value' => '42 King Street West',
                    ],
                    'country_id' => [
                        'value' => 'United Kingdom',
                        'input' => 'select',
                    ],
                    'region' => [
                        'value' => 'Manchester',
                        'selector' => '#region',
                    ],
                    'city' => [
                        'value' => 'Manchester',
                    ],
                    'postcode' => [
                        'value' => 'M3 2WY',
                        'selector' => '#zip',
                    ],
                ],
            ]
        ];
    }

    /**
     * Get address data for UK with VAT
     *
     * @param array $defaultData
     * @return array
     */
    protected function getAddressUKWithVAT($defaultData)
    {
        return array_replace_recursive(
            $defaultData,
            [
                'data' => [
                    'fields' => [
                        'vat_id' => [
                            'value' => '584451913',
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Get address for Germany
     *
     * @return array
     */
    protected function getAddressDE()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'Jan',
                    ],
                    'lastname' => [
                        'value' => 'Jansen',
                    ],
                    'company' => [
                        'value' => 'Magento %isolation%',
                    ],
                    'country_id' => [
                        'value' => 'Germany',
                        'input' => 'select',
                    ],
                    'street' => [
                        'value' => 'Augsburger Strabe 41',
                    ],
                    'city' => [
                        'value' => 'Berlin',
                    ],
                    'region_id' => [
                        'value' => 'Berlin',
                        'input' => 'select',
                        'selector' => '#region_id',
                    ],
                    'postcode' => [
                        'value' => '10789',
                    ],
                    'telephone' => [
                        'value' => '333-33-333-33',
                    ],
                ],
            ]
        ];
    }
}
