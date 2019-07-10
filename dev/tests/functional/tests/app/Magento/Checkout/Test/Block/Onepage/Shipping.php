<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Block\Onepage;

use Magento\Checkout\Test\Block\Onepage\Shipping\AddressModal;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Checkout shipping address block.
 */
class Shipping extends Form
{
    /**
     * CSS Selector for "New Address" button.
     *
     * @var string
     */
    private $newAddressButton = '[data-bind*="isNewAddressAdded"]';

    /**
     * CSS Selector for "Edit" button.
     *
     * @var string
     */
    private $editAddressButton = '.edit-address-link';

    /**
     * Wait element.
     *
     * @var string
     */
    private $waitElement = '.loading-mask';

    /**
     * SCC Selector for Address Modal block.
     *
     * @var string
     */
    private $addressModalBlock = '//*[@id="opc-new-shipping-address"]/../..';

    /**
     * @var string
     */
    private $selectedAddress = '.shipping-address-item.selected-item';

    /**
     * New address button selector.
     *
     * @var string
     */
    private $popupSelector = '.action-show-popup';

    /**
     * Locator for address select button.
     *
     * @var string
     */
    private $addressSelectButton = '.action-select-shipping-item';

    /**
     * Locator for shipping address select block.
     *
     * @var string
     */
    private $shippingAddressBlock = '.shipping-address-item';

    /**
     * Locator for shipping address select block.
     *
     * @var string
     */
    private $selectedShippingAddressBlock = '.selected-item';

    /**
     * Email instructions selector.
     *
     * @var string
     */
    private $emailInstructions = '#customer-email-fieldset .note span';

    /**
     * Email tooltip button selector.
     *
     * @var string
     */
    private $emailTooltipButton = '#customer-email-fieldset .field-tooltip-action';

    /**
     * Email tooltip content selector.
     *
     * @var string
     */
    private $emailTooltipContent = '#customer-email-fieldset .field-tooltip-content';

    /**
     * Email error selector.
     *
     * @var string
     */
    private $emailError = '#checkout-customer-email-error';

    /**
     * Get email error.
     *
     * @return string
     */
    public function getEmailError()
    {
        return $this->_rootElement->find($this->emailError)->getText();
    }

    /**
     * Get email tooltip.
     *
     * @return string
     */
    public function getEmailTooltip()
    {
        $this->_rootElement->find($this->emailTooltipButton)->click();
        return $this->_rootElement->find($this->emailTooltipContent)->getText();
    }

    /**
     * Get email instructions.
     *
     * @return string
     */
    public function getEmailInstructions()
    {
        return $this->_rootElement->find($this->emailInstructions)->getText();
    }

    /**
     * Click on "New Address" button.
     *
     * @return void
     */
    public function clickOnNewAddressButton()
    {
        $this->waitForElementNotVisible($this->waitElement);
        $this->_rootElement->find($this->newAddressButton)->click();
    }

    /**
     * Get Address Modal Block.
     *
     * @return AddressModal
     */
    public function getAddressModalBlock()
    {
        return $this->blockFactory->create(
            AddressModal::class,
            ['element' => $this->browser->find($this->addressModalBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Returns form's required elements
     *
     * @return \Magento\Mtf\Client\ElementInterface[]
     */
    public function getRequiredFields()
    {
        return $this->_rootElement->getElements("div .field._required");
    }

    /**
     * @return array
     */
    public function getSelectedAddress()
    {
        return $this->_rootElement->find($this->selectedAddress, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get address block.
     *
     * @param String $address
     */
    public function editAddress($address)
    {
        $addresses = $this->_rootElement->getElements($this->shippingAddressBlock);
        foreach ($addresses as $addressBlock) {
            if (strpos($addressBlock->getText(), $address) === 0) {
                $addressBlock->find($this->editAddressButton)->click();
                break;
            }
        }
    }

    /**
     * Select address.
     *
     * @param string $address
     * @return void
     */
    public function selectAddress($address)
    {
        $addresses = $this->_rootElement->getElements($this->shippingAddressBlock);
        foreach ($addresses as $addressBlock) {
            if (strpos($addressBlock->getText(), $address) === 0 && !$this->isAddressSelected($address)) {
                $addressBlock->find($this->addressSelectButton)->click();
                break;
            }
        }
    }

    /**
     * Check if address selected.
     *
     * @param string $address
     * @return bool
     */
    public function isAddressSelected($address)
    {
        $text = $this->_rootElement->find($this->shippingAddressBlock . $this->selectedShippingAddressBlock)->getText();

        return $text == $address;
    }

    /**
     * Checks if new address button is visible.
     *
     * @return bool
     */
    public function isPopupNewAddressButtonVisible()
    {
        $button = $this->_rootElement->find($this->popupSelector);
        return $button->isVisible();
    }

    /**
     * Clicks new address button.
     *
     * @return void
     */
    public function clickPopupNewAddressButton()
    {
        $this->_rootElement->find($this->popupSelector)->click();
    }
}
