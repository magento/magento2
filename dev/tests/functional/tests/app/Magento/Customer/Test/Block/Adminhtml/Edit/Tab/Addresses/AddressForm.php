<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Block\Form;

/**
 * Create/Edit customer address.
 */
class AddressForm extends Form
{
    /**
     * Save address button
     *
     * @var string
     */
    protected $saveAddressButton = '#save';

    /**
     * cancel button
     *
     * @var string
     */
    protected $cancelButton = '#cancel';

    /**
     * Loader mask
     *
     * @var string
     */
    private $loader = '.popup-loading';

    /**
     * Field with Mage error.
     *
     * @var string
     */
    private $mageErrorField = '//fieldset/*[contains(@class,"field ")][.//*[contains(@class,"error")]]';

    /**
     * Fields label with mage error.
     *
     * @var string
     */
    private $mageErrorLabel = './/*[contains(@class,"label")]';

    /**
     * Mage error text.
     *
     * @var string
     */
    private $mageErrorText = './/label[contains(@class,"error")]';

    /**
     * Fill address form by provided data
     *
     * @param \Magento\Mtf\Fixture\FixtureInterface $address
     * @return void
     * @throws \Exception
     */
    public function fillAddressData(\Magento\Mtf\Fixture\FixtureInterface $address)
    {
        $this->waitForElementNotVisible($this->loader);
        $this->setFieldsData($address->getData(), $this->_rootElement);
    }

    /**
     * Fill data into fields in the container.
     *
     * @param array $fields
     * @param \Magento\Mtf\Client\Element\SimpleElement|null $contextElement
     * @return void
     * @throws \Exception
     */
    public function setFieldsData(array $fields, \Magento\Mtf\Client\Element\SimpleElement $contextElement = null): void
    {
        $data = $this->dataMapping($fields);
        $this->_fill($data, $contextElement);
    }

    /**
     * Save customer address
     *
     * @return void
     */
    public function saveAddress(): void
    {
        $this->_rootElement->find($this->saveAddressButton)->click();
        $this->waitForElementNotVisible($this->loader);
    }

    /**
     * Close create/update address modal
     *
     * @return void
     */
    public function clickCancelButton(): void
    {
        $this->_rootElement->find($this->cancelButton)->click();
    }

    /**
     * Get array of label => js error text.
     *
     * @return array
     */
    public function getJsErrors(): array
    {
        $data = [];
        $elements = $this->_rootElement->getElements($this->mageErrorField, Locator::SELECTOR_XPATH);
        foreach ($elements as $element) {
            $error = $element->find($this->mageErrorText, Locator::SELECTOR_XPATH);
            if ($error->isVisible()) {
                $label = $element->find($this->mageErrorLabel, Locator::SELECTOR_XPATH)->getText();
                $data[$label] = $error->getText();
            }
        }
        return $data;
    }
}
