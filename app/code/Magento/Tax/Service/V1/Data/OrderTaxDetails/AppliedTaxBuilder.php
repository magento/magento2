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
namespace Magento\Tax\Service\V1\Data\OrderTaxDetails;

/**
 * Builder for the AppliedTax Data Object
 *
 * @method AppliedTax create()
 */
class AppliedTaxBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set tax rate code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->_set(AppliedTax::KEY_CODE, $code);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->_set(AppliedTax::KEY_TITLE, $title);
    }

    /**
     * Set percent
     *
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent)
    {
        return $this->_set(AppliedTax::KEY_PERCENT, $percent);
    }

    /**
     * Set amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        return $this->_set(AppliedTax::KEY_AMOUNT, $amount);
    }

    /**
     * Set base amount
     *
     * @param float $baseAmount
     * @return $this
     */
    public function setBaseAmount($baseAmount)
    {
        return $this->_set(AppliedTax::KEY_BASE_AMOUNT, $baseAmount);
    }
}
