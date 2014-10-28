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
namespace Magento\Tax\Service\V1\Data\TaxDetails;

class AppliedTax extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_TAX_RATE_KEY = 'tax_rate_key';

    const KEY_PERCENT = 'percent';

    const KEY_AMOUNT = 'amount';

    const KEY_RATES = 'rates';
    /**#@-*/

    /**
     * Get tax rate key
     *
     * @return string|null
     */
    public function getTaxRateKey()
    {
        return $this->_get(self::KEY_TAX_RATE_KEY);
    }

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent()
    {
        return $this->_get(self::KEY_PERCENT);
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_get(self::KEY_AMOUNT);
    }

    /**
     * Get rates
     *
     * @return \Magento\Tax\Service\V1\Data\TaxDetails\AppliedTaxRate[]|null
     */
    public function getRates()
    {
        return $this->_get(self::KEY_RATES);
    }
}
