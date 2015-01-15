<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class TaxRule Repository
 */
class TaxRule extends AbstractRepository
{
    /**
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['custom_rule'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'us_ca_rate_8_25',
                    1 => 'us_ny_rate_8_375',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];

        $this->_data['us_ca_ny_rule'] = [
            'code' => 'Tax Rule %isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'US-CA-*-Rate 1',
                    1 => 'us_ny_rate_8_1',
                ],
            ],
            'tax_customer_class' => [
                'dataSet' => [
                    0 => 'Retail Customer',
                    1 => 'customer_tax_class',
                ],
            ],
            'tax_product_class' => [
                'dataSet' => [
                    0 => 'Taxable Goods',
                    1 => 'product_tax_class',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];

        $this->_data['uk_full_tax_rule'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'uk_full_tax_rate',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];

        $this->_data['tax_rule_default'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'US-CA-*-Rate 1',
                ],
            ],
            'tax_customer_class' => [
                'dataSet' => [
                    0 => 'Retail Customer',
                ],
            ],
            'tax_product_class' => [
                'dataSet' => [
                    0 => 'Taxable Goods',
                ],
            ],
            'priority' => '1',
            'position' => '1',
        ];

        $this->_data['tax_rule_with_custom_tax_classes'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'US-CA-*-Rate 1',
                    1 => 'US-NY-*-Rate 1',
                ],
            ],
            'tax_customer_class' => [
                'dataSet' => [
                    0 => 'Retail Customer',
                    1 => 'customer_tax_class',
                ],
            ],
            'tax_product_class' => [
                'dataSet' => [
                    0 => 'product_tax_class',
                    1 => 'Taxable Goods',
                ],
            ],
            'priority' => '1',
            'position' => '1',
        ];

        $this->_data['customer_equals_store_rate'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'us_ca_rate_8_25_no_zip',
                    1 => 'us_ny_rate_8_25',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];

        $this->_data['customer_less_store_rate'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'us_ca_rate_8_375',
                    1 => 'us_ny_rate_8_25',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];

        $this->_data['customer_greater_store_rate'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'us_ca_rate_8_25_no_zip',
                    1 => 'us_ny_rate_8_375',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];

        $this->_data['cross_border_tax_rule'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'tx_rate_10',
                    1 => 'ny_rate_20',
                    2 => 'ca_rate_30',
                ],
            ],
            'priority' => '0',
            'position' => '0',
        ];
    }
}
