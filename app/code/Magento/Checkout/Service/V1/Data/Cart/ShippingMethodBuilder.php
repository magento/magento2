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
 * Builder for the Shipping Method Data
 */
class ShippingMethodBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set carrier code
     *
     * @param string $value
     * @return $this
     */
    public function setCarrierCode($value)
    {
        return $this->_set(ShippingMethod::CARRIER_CODE, $value);
    }

    /**
     * Set shipping method code
     *
     * @param string $value
     * @return $this
     */
    public function setMethodCode($value)
    {
        return $this->_set(ShippingMethod::METHOD_CODE, $value);
    }

    /**
     * Set shipping carrier title
     *
     * @param string $value
     * @return $this
     */
    public function setCarrierTitle($value)
    {
        return $this->_set(ShippingMethod::CARRIER_TITLE, $value);
    }

    /**
     * Set shipping method title
     *
     * @param string $value
     * @return $this
     */
    public function setMethodTitle($value)
    {
        return $this->_set(ShippingMethod::METHOD_TITLE, $value);
    }

    /**
     * Set shipping amount
     *
     * @param float $value
     * @return $this
     */
    public function setAmount($value)
    {
        return $this->_set(ShippingMethod::SHIPPING_AMOUNT, $value);
    }

    /**
     * Set base shipping amount
     *
     * @param float $value
     * @return $this
     */
    public function setBaseAmount($value)
    {
        return $this->_set(ShippingMethod::BASE_SHIPPING_AMOUNT, $value);
    }

    /**
     * Set method availability flag
     *
     * @param bool $value
     * @return $this
     */
    public function setAvailable($value)
    {
        return $this->_set(ShippingMethod::AVAILABLE, (bool)$value);
    }
}
