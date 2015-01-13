<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Customer Repository
 *
 */
class Customer extends AbstractRepository
{
    /**
     * The group ID for customer fields
     */
    const GROUP_CUSTOMER_INFO_TABS_ACCOUNT = 'account_information';

    /**
     * The 'value' key for group entries
     */
    const INDEX_VALUE = 'value';

    /**
     * The 'input_value' key for group entries
     */
    const INDEX_INPUT_VALUE = 'input_value';

    /**
     * @var array attributes that represent a group type of 'General'
     */
    protected $groupGeneral = [self::INDEX_VALUE => 'General', self::INDEX_INPUT_VALUE => '1'];

    /**
     * @var array attributes that represent a group type of 'Retailer'
     */
    protected $groupRetailer = [self::INDEX_VALUE => 'Retailer', self::INDEX_INPUT_VALUE => '3'];

    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];

        $this->_data['customer_US_1'] = $this->_getUS1();
        $this->_data['backend_customer'] = $this->_getBackendCustomer($this->groupGeneral);
        $this->_data['backend_retailer_customer'] = $this->_getBackendCustomer($this->groupRetailer);
        $this->_data['customer_UK_1'] = $this->getUK1();
        $this->_data['customer_UK_with_VAT'] = $this->getUKWithVAT($this->_data['customer_UK_1']);
        $this->_data['customer_DE_1'] = $this->getDE1();
    }

    protected function _getUS1()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'John',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'email' => [
                        'value' => 'John.Doe%isolation%@example.com',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'password' => [
                        'value' => '123123q',
                    ],
                    'password_confirmation' => [
                        'value' => '123123q',
                    ],
                ],
                'address' => [
                    'dataset' => [
                        'value' => 'address_US_1',
                    ],
                ],
            ]
        ];
    }

    /**
     * Get customer from Germany
     *
     * @return array
     */
    protected function getDE1()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'Jan',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'lastname' => [
                        'value' => 'Jansen',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'email' => [
                        'value' => 'Jan.Jansen%isolation%@example.com',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'password' => [
                        'value' => '123123q',
                    ],
                    'password_confirmation' => [
                        'value' => '123123q',
                    ],
                ],
            ]
        ];
    }

    protected function _getBackendCustomer($groupType)
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'John',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'email' => [
                        'value' => 'John.Doe%isolation%@example.com',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'website_id' => [
                        'value' => 'Main Website',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                        'input' => 'select',
                        'input_value' => '1',
                    ],
                    'group_id' => [
                        'value' => $groupType[self::INDEX_VALUE],
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                        'input' => 'select',
                        'input_value' => $groupType[self::INDEX_INPUT_VALUE],
                    ],
                    'password' => [
                        'value' => '123123q',
                        'group' => null,
                    ],
                    'password_confirmation' => [
                        'value' => '123123q',
                        'group' => null,
                    ],
                ],
                'address' => [
                    'dataset' => [
                        'value' => 'address_US_1',
                    ],
                ],
                'addresses' => [],
            ]
        ];
    }

    /**
     * Get customer data for UK
     *
     * @return array
     */
    protected function getUK1()
    {
        return [
            'data' => [
                'fields' => [
                    'firstname' => [
                        'value' => 'Jane',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'lastname' => [
                        'value' => 'Doe',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'email' => [
                        'value' => 'Jane.Doe%isolation%@example.com',
                        'group' => self::GROUP_CUSTOMER_INFO_TABS_ACCOUNT,
                    ],
                    'password' => [
                        'value' => '123123q',
                    ],
                    'password_confirmation' => [
                        'value' => '123123q',
                    ],
                ],
                'address' => [
                    'dataset' => [
                        'value' => 'address_UK',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get customer data for UK with VAT number
     *
     * @param array $defaultData
     * @return array
     */
    protected function getUKWithVAT($defaultData)
    {
        return array_replace_recursive(
            $defaultData,
            [
                'data' => [
                    'address' => [
                        'dataset' => [
                            'value' => 'address_UK_with_VAT',
                        ],
                    ],
                ],
            ]
        );
    }
}
