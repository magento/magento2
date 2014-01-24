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


/**
 * Collection of tax module calls
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Helper\Product;

class Price extends \Magento\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Tax\Model\Calculation $taxCalculation
    ) {
        $this->taxData = $taxData;
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * Get product price with all tax settings processing
     *
     * @param $_product
     * @param $_minimalPriceValue
     * @param null $includingTax
     * @return float
     */
    public function getPrice($_product, $_minimalPriceValue, $includingTax = null)
    {
        return $this->taxData->getPrice($_product, $_minimalPriceValue, $includingTax);
    }

    /**
     * Check if we have display in catalog prices including and excluding tax
     *
     * @return bool
     */
    public function displayBothPrices()
    {
        return $this->taxData->displayBothPrices();
    }

    /**
     * Check if we have display in catalog prices including tax
     *
     * @return bool
     */
    public function displayPriceIncludingTax()
    {
        return $this->taxData->displayPriceIncludingTax();
    }

    /**
     * Check if product prices on input include tax
     *
     * @return bool
     */
    public function priceIncludesTax()
    {
        return $this->taxData->priceIncludesTax();
    }

    /**
     * Get customer object
     *
     * @return bool|\Magento\Customer\Model\Customer
     */
    public function getCustomer()
    {
        return $this->taxCalculation->getCustomer();
    }

    /**
     * Specify customer object which can be used for rate calculation
     *
     * @param   \Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Model\Customer $customer)
    {
        $this->taxCalculation->setCustomer($customer);
        return $this;
    }

    /**
     * Get request object with information necessary for getting tax rate
     *
     * @param   null|bool|\Magento\Object $shippingAddress
     * @param   null|bool||\Magento\Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  \Magento\Object
     */
    public function getRateRequest(
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null
    ) {
        return $this->taxCalculation->getRateRequest($shippingAddress, $billingAddress, $customerTaxClass, $store);
    }

    /**
     * Get calculation tax rate by specific request
     *
     * @param   \Magento\Object $request
     * @return  float
     */
    public function getRate($request)
    {
        return $this->taxCalculation->getRate($request);
    }
}
