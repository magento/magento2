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
namespace Magento\Payment\Model\Cart\SalesModel;

/**
 * Wrapper interface for accessing sales model data
 */
interface SalesModelInterface
{
    /**
     * Get all items from shopping sales model
     *
     * @return array
     */
    public function getAllItems();

    /**
     * @return float|null
     */
    public function getBaseSubtotal();

    /**
     * @return float|null
     */
    public function getBaseTaxAmount();

    /**
     * @return float|null
     */
    public function getBaseShippingAmount();

    /**
     * @return float|null
     */
    public function getBaseDiscountAmount();

    /**
     * Wrapper for \Magento\Framework\Object getDataUsingMethod method
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function getDataUsingMethod($key, $args = null);

    /**
     * Return object that contains tax related fields
     *
     * @return \Magento\Sales\Model\Order|\Magento\Sales\Model\Quote\Address
     */
    public function getTaxContainer();
}
