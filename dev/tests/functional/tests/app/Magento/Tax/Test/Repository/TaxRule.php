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
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['custom_rule'] = [
            'code' => 'TaxIdentifier%isolation%',
            'tax_rate' => [
                'dataSet' => [
                    0 => 'us_ca_rate_8_25',
                    1 => 'us_ny_rate_8_375',
                ]
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
                    0 => 'US-CA-*-Rate 1'
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
    }
}
