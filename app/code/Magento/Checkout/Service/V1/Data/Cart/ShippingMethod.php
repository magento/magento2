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
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Quote shipping method data
 *
 * @codeCoverageIgnore
 */
class ShippingMethod extends \Magento\Framework\Service\Data\AbstractExtensibleObject
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const CARRIER_CODE = 'carrier_code';

    const METHOD_CODE = 'method_code';

    const CARRIER_TITLE = 'carrier_title';

    const METHOD_TITLE = 'method_title';

    const SHIPPING_AMOUNT = 'amount';

    const BASE_SHIPPING_AMOUNT = 'base_amount';

    const AVAILABLE = 'available';
    /**#@-*/

    /**
     * Get carrier code
     *
     * @return string
     */
    public function getCarrierCode()
    {
        return $this->_get(self::CARRIER_CODE);
    }

    /**
     * Get shipping method code
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_get(self::METHOD_CODE);
    }

    /**
     * Get shipping carrier title
     *
     * @return string|null
     */
    public function getCarrierTitle()
    {
        return $this->_get(self::CARRIER_TITLE);
    }

    /**
     * Get shipping method title
     *
     * @return string|null
     */
    public function getMethodTitle()
    {
        return $this->_get(self::METHOD_TITLE);
    }

    /**
     * Get shipping amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->_get(self::SHIPPING_AMOUNT);
    }

    /**
     * Get base shipping amount
     *
     * @return float
     */
    public function getBaseAmount()
    {
        return $this->_get(self::BASE_SHIPPING_AMOUNT);
    }

    /**
     * Get availability flag of current method
     *
     * @return bool
     */
    public function getAvailable()
    {
        return $this->_get(self::AVAILABLE);
    }
}
