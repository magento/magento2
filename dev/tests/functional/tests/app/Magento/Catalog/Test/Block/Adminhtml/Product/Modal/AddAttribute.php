<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Modal;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Backend\Test\Block\Template;
use Magento\Mtf\Client\Locator;

/**
 * Add new attribute modal.
 */
class AddAttribute extends FormSections
{
    /**
     * Selector for "Create New Attribute" button.
     *
     * @var string
     */
    private $createNewAttribute = 'button[data-index="add_new_attribute_button"]';

    /**
     * Xpath selector for "Add Attribute" form.
     *
     * @var string
     */
    private $addAttributeBlock = '//*[@data-role="modal"][.//button[@data-index="add_new_attribute_button"]]';

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->addAttributeBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Click on "Create new attribute" button.
     *
     * @return void
     */
    public function createNewAttribute()
    {
        $this->getTemplateBlock()->waitLoader();
        $this->_rootElement->find($this->createNewAttribute)->click();
        $this->getTemplateBlock()->waitLoader();
    }
}
