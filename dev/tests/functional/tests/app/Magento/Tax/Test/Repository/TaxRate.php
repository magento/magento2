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
 * Class Tax Rate Repository
 *
 */
class TaxRate extends AbstractRepository
{
    /**
     * Initialize default parameters
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

        $this->_data['us_ca_rate_8_25'] = array_replace_recursive($this->_data['default'], $this->_getRateUSCA());
        $this->_data['us_ny_rate_8_375'] = array_replace_recursive($this->_data['default'], $this->_getRateUSNY());
        $this->_data['us_ny_rate_8_1'] = array_replace_recursive($this->_data['default'], $this->_getRateUSNYCustom());
        $this->_data['paypal_rate_8_25'] = array_replace_recursive($this->_data['default'], $this->_getRatePayPal());
        $this->_data['uk_full_tax_rate'] = $this->getUKFullTaxRate($this->_data['default']);
    }

    /**
     * Rate US CA with 8.25%
     *
     * @return array
     */
    protected function _getRateUSCA()
    {
        return array(
            'data' => array(
                'fields' => array(
                    'rate' => array(
                        'value' => '8.25'
                    ),
                    'tax_postcode' => array(
                        'value' => '90230'
                    ),
                    'tax_region_id' => array(
                        'value' => '12' // California
                    )
                )
            )
        );
    }

    /**
     * Rate US CA with 8.25%
     *
     * @return array
     */
    protected function _getRatePayPal()
    {
        return array(
            'data' => array(
                'fields' => array(
                    'rate' => array(
                        'value' => '8.25'
                    ),
                    'tax_postcode' => array(
                        'value' => '95131'
                    ),
                    'tax_region_id' => array(
                        'value' => '12' // California
                    )
                )
            )
        );
    }

    /**
     * Rate US NY with 8.375%
     *
     * @return array
     */
    protected function _getRateUSNY()
    {
        return array(
            'data' => array(
                'fields' => array(
                    'rate' => array(
                        'value' => '8.375'
                    ),
                    'tax_region_id' => array(
                        'value' => '43' // New York
                    )
                )
            )
        );
    }

    /**
     * Rate US NY with 8.1%
     *
     * @return array
     */
    protected function _getRateUSNYCustom()
    {
        return array(
            'data' => array(
                'fields' => array(
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
            )
        );
    }

    /**
     * Get UK full tax rate
     *
     * @param array $defaultData
     * @return array
     */
    protected function getUKFullTaxRate($defaultData)
    {
        return array_replace_recursive(
            $defaultData,
            array(
                'data' => array(
                    'fields' => array(
                        'rate' => array(
                            'value' => 20
                        ),
                        'tax_country_id' => array(
                            'value' => 'GB',
                        ),
                    ),
                ),
            )
        );
    }
}
