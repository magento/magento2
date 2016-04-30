<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create\Shipping;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Template;

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
     * Shipping address title selector.
     *
     * @var string
     */
    protected $title = 'legend';

    /**
     * Check the 'Same as billing address' checkbox in shipping address.
     *
     * @return void
     */
    public function setSameAsBillingShippingAddress()
    {
        $this->_rootElement->find($this->title)->click();
        $this->_rootElement->find($this->sameAsBilling, Locator::SELECTOR_CSS, 'checkbox')->setValue('Yes');
    }

    /**
     * Uncheck the 'Same as billing address' checkbox in shipping address.
     *
     * @return void
     */
    public function uncheckSameAsBillingShippingAddress()
    {
        $this->_rootElement->find($this->title)->click();
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
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }
}
