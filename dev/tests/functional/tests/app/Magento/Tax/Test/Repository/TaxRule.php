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
 * Class Tax Rule Repository
 *
 */
class TaxRule extends AbstractRepository
{
    /**
     * Initialize repository data
     *
     * @param array $defaultConfig
     * @param array $defaultData
     */
    public function __construct(array $defaultConfig = array(), array $defaultData = array())
    {
        $this->_data['default'] = array(
            'config' => $defaultConfig,
            'data' => $defaultData
        );

        $this->_data['custom_rule'] = array_replace_recursive($this->_data['default'], $this->_getCustomTaxRule());
        $this->_data['us_ca_ny_rule'] = $this->_getUscanyTaxRule();
        $this->_data['uk_full_tax_rule'] = $this->getUKFullTaxRule($this->_data['default']);
    }

    /**
     * Return data structure for Tax Rule with custom Rates, Tax class
     *
     * @return array
     */
    protected function _getCustomTaxRule()
    {
        return array(
            'data' => array(
                'fields' => array(
                    'tax_rate[0]' => array(
                        'value' => '%us_ca_rate_8_25%'
                    ),
                    'tax_rate[1]' => array(
                        'value' => '%us_ny_rate_8_375%'
                    ),
                )
            )
        );
    }

    /**
     * Return data structure for Tax Rule with custom Rates, Tax classes
     *
     * @return array
     */
    protected function _getUscanyTaxRule()
    {
        return array(
            'data' => array(
                'fields' => array(
                    'code' => array(
                        'value' => 'Tax Rule %isolation%'
                    ),
                    'tax_rate' => array(
                        array(
                            'code' => array(
                                'value' => 'US-CA-*-Rate 1'
                            )
                        ),
                        array(
                            'code' => array(
                                'value' => 'US-NY-*-%isolation%'
                            ),
                            'rate' => array(
                                'value' => '8.1'
                            ),
                            'tax_region_id' => array(
                                'value' => 'New York',
                                'input' => 'select'
                            )
                        )
                    ),
                    'tax_customer_class' => array(
                        'value' => array(
                            'Retail Customer',
                            'Customer Tax Class %isolation%'
                        )
                    ),
                    'tax_product_class' => array(
                        'value' => array(
                            'Taxable Goods',
                            'Product Tax Class %isolation%'
                        )
                    ),
                    'priority' => array(
                        'value' => '0'
                    ),
                    'position' => array(
                        'value' => '0'
                    )
                )
            )
        );
    }

    /**
     * Get UK full tax rule
     *
     * @param array $defaultData
     * @return array
     */
    protected function getUKFullTaxRule($defaultData)
    {
        return array_replace_recursive(
            $defaultData,
            array(
                'data' => array(
                    'fields' => array(
                        'tax_rate' => array(
                            'value' => '%uk_full_tax_rate%'
                        ),
                    ),
                ),
            )
        );
    }
}
