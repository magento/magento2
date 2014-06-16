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

namespace Magento\Checkout\Test\Block\Cart;

use Mtf\Block\Form;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Magento\Customer\Test\Fixture\AddressInjectable;

/**
 * Class Shipping
 * Cart shipping block
 */
class Shipping extends Form
{
    /**
     * Form wrapper selector
     *
     * @var string
     */
    protected $formWrapper = '.content';

    /**
     * Open shipping form selector
     *
     * @var string
     */
    protected $openForm = '.title';

    /**
     * Get quote selector
     *
     * @var string
     */
    protected $getQuote = '.action.quote';

    /**
     * Update total selector
     *
     * @var string
     */
    protected $updateTotalSelector = '.action.update';

    /**
     * Selector to access the shipping carrier method
     *
     * @var string
     */
    protected $shippingMethod = '//span[text()="%s"]/following::*//*[contains(text(), "%s")]';

    /**
     * From with shipping available shipping methods
     *
     * @var string
     */
    protected $shippingMethodForm = '#co-shipping-method-form';

    /**
     * Open estimate shipping and tax form
     *
     * @return void
     */
    public function openEstimateShippingAndTax()
    {
        if (!$this->_rootElement->find($this->formWrapper)->isVisible()) {
            $this->_rootElement->find($this->openForm)->click();
        }
    }

    /**
     * Click Get quote button
     *
     * @return void
     */
    public function clickGetQuote()
    {
        $this->_rootElement->find($this->getQuote)->click();
    }

    /**
     * Select shipping method
     *
     * @param array $shipping
     * @return void
     */
    public function selectShippingMethod(array $shipping)
    {
        $selector = sprintf($this->shippingMethod, $shipping['carrier'], $shipping['method']);
        if (!$this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isVisible()) {
            $this->openEstimateShippingAndTax();
        }
        $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->click();
        $this->_rootElement->find($this->updateTotalSelector, Locator::SELECTOR_CSS)->click();
    }

    /**
     * Fill shipping and tax form
     *
     * @param AddressInjectable $address
     * @return void
     */
    public function fillEstimateShippingAndTax(AddressInjectable $address)
    {
        $this->openEstimateShippingAndTax();
        $this->fill($address);
        $this->clickGetQuote();
    }

    /**
     * Determines if the specified shipping carrier/method is visible on the cart
     *
     * @param $carrier
     * @param $method
     * @return bool
     */
    public function isShippingCarrierMethodVisible($carrier, $method)
    {
        $shippingMethodForm = $this->_rootElement->find($this->shippingMethodForm);
        $this->_rootElement->waitUntil(
            function () use ($shippingMethodForm) {
                return $shippingMethodForm->isVisible() ? true : null;
            }
        );
        $selector = sprintf($this->shippingMethod, $carrier, $method);
        return $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isVisible();
    }
}
