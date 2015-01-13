<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration\Edit;

use Magento\Backend\Test\Block\FormPageActions;

/**
 * Class IntegrationFormPageActions
 * Form page actions block in Integration new/edit page
 */
class IntegrationFormPageActions extends FormPageActions
{
    /**
     * Save button
     *
     * @var string
     */
    protected $saveNewButton = '[data-ui-id="integration-edit-content-save-split-button-button"]';

    /**
     * Click on "Save" with split button
     *
     * @return void
     */
    public function saveNew()
    {
        $this->_rootElement->find($this->saveNewButton)->click();
    }
}
