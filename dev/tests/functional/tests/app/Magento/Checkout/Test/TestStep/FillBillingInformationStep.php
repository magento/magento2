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

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\AddressInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\TestStep\TestStepInterface;

/**
 * Class FillBillingInformationStep
 * Fill billing information
 */
class FillBillingInformationStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Address fixture
     *
     * @var AddressInjectable
     */
    protected $billingAddress;

    /**
     * Customer fixture
     *
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * Checkout method
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param AddressInjectable $billingAddress
     * @param CustomerInjectable $customer
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        AddressInjectable $billingAddress,
        CustomerInjectable $customer,
        $checkoutMethod
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->billingAddress = $billingAddress;
        $this->customer = $customer;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Fill billing address
     *
     * @return void
     */
    public function run()
    {
        $customer = $this->checkoutMethod === 'register' ? $this->customer : null;
        $this->checkoutOnepage->getBillingBlock()->fillBilling($this->billingAddress, $customer);
        $this->checkoutOnepage->getBillingBlock()->clickContinue();
    }
}
