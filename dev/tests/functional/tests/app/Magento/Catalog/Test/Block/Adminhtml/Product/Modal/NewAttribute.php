<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Modal;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Backend\Test\Block\FormPageActions;
use Magento\Mtf\Client\Locator;

/**
 * Product new attribute modal.
 */
class NewAttribute extends FormSections
{
    /**
     * Xpath selector for "New Attribute" form.
     *
     * @var string
     */
    private $newAttributeBlock = '//*[@data-role="modal"][.//input[@name="frontend_label[0]"]]';

    /**
     * Get form page actions block.
     *
     * @return FormPageActions
     */
    protected function getFormPageActionsBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\FormPageActions',
            ['element' => $this->_rootElement->find($this->newAttributeBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Click "Save Attribute" button on attribute form.
     *
     * @return void
     */
    public function saveAttribute()
    {
        $this->getFormPageActionsBlock()->save();
    }
}
