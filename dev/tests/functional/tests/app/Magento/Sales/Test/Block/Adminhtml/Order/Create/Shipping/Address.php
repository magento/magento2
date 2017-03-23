<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Adminhtml sales order create shipping address block.
 */
class Address extends Form
{
    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * 'Same as billing address' checkbox.
     *
     * @var string
     */
    protected $sameAsBilling = '#order-shipping_same_as_billing';

    /**
     * Wait element.
     *
     * @var string
     */
    private $waitElement = '.loading-mask';

    /**
     * Shipping address title selector.
     *
     * @var string
     */
    protected $title = 'legend';

    /**
     * Sales order create account information block.
     *
     * @var string
     */
    private $accountInformationBlock = '#order-form_account';

    /**
     * Check the 'Same as billing address' checkbox in shipping address.
     *
     * @return void
     */
    public function setSameAsBillingShippingAddress()
    {
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->hover();
        $this->browser->find($this->accountInformationBlock)->hover();
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
    }

    /**
     * Uncheck the 'Same as billing address' checkbox in shipping address.
     *
     * @return void
     */
    public function uncheckSameAsBillingShippingAddress()
    {
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->hover();
        $this->browser->find($this->accountInformationBlock)->hover();
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->setValue('No');
        $this->waitLoader();
    }

    /**
     * Wait load block.
     *
     * @return void
     */
    protected function waitLoader()
    {
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->waitUntil(
            function () {
                return $this->_rootElement->find($this->title)->isVisible() ? true : null;
            }
        );
    }

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill specified form data.
     *
     * @param array $fields
     * @param SimpleElement $element
     * @return void
     * @throws \Exception
     */
    protected function _fill(array $fields, SimpleElement $element = null)
    {
        $context = ($element === null) ? $this->_rootElement : $element;
        foreach ($fields as $name => $field) {
            $this->waitFormLoading();
            $element = $this->getElement($context, $field);
            if (!$element->isDisabled()) {
                $element->setValue($field['value']);
            } else {
                throw new \Exception("Unable to set value to field '$name' as it's disabled.");
            }
        }
    }

    /**
     * Wait for form loading.
     *
     * @return void
     */
    private function waitFormLoading()
    {
        $this->_rootElement->click();
        $this->browser->waitUntil(
            function () {
                return $this->browser->find($this->waitElement)->isVisible() ? null : true;
            }
        );
    }
}
