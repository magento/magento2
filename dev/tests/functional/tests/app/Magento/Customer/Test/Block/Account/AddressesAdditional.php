<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Block\Account;

use Magento\Customer\Test\Fixture\Address;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Class AddressesAdditional
 * Additional Addresses block
 */
class AddressesAdditional extends Block
{
    /**
     * Selector for address block
     *
     * @var string
     */
    protected $addressSelector = '//li[address[contains(.,"%s")]]';

    /**
     * Selector for addresses block
     *
     * @var string
     */
    protected $addressesSelector = '//li[address]';

    /**
     * Selector for delete link
     *
     * @var string
     */
    protected $deleteAddressLink = "[role='delete-address']";

    /**
     * Content of additional address block
     *
     * @var string
     */
    protected $additionalAddressContent = '.block-content';

    /**
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

    /**
     * Delete Additional Address
     *
     * @param Address $address
     * @return void
     */
    public function deleteAdditionalAddress(Address $address)
    {
        $this->_rootElement->find(sprintf($this->addressSelector, $address->getStreet()), Locator::SELECTOR_XPATH)
            ->find($this->deleteAddressLink)->click();
        $element = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(\Magento\Ui\Test\Block\Adminhtml\Modal::class, ['element' => $element]);
        $modal->acceptAlert();
    }

    /**
     * Check if additional address exists.
     *
     * @param string $address
     * @return boolean
     */
    public function isAdditionalAddressExists($address)
    {
        $additionalAddressExists = false;

        $addresses = $this->_rootElement->getElements($this->addressesSelector, Locator::SELECTOR_XPATH);
        foreach ($addresses as $addressBlock) {
            if (strpos($addressBlock->getText(), $address) === 0) {
                $additionalAddressExists = $addressBlock->isVisible();
                break;
            }
        }
        return $additionalAddressExists;
    }

    /**
     * Get block text
     *
     * @return string
     */
    public function getBlockText()
    {
        return $this->_rootElement->find($this->additionalAddressContent)->getText();
    }
}
