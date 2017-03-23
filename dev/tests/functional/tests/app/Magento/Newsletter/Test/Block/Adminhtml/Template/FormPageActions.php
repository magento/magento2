<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Block\Adminhtml\Template;

use Magento\Backend\Test\Block\FormPageActions as AbstractFormPageActions;

/**
 * Class FormPageActions
 * Form page actions block
 *
 * @package Magento\Newsletter\Test\Block\Adminhtml\Template
 */
class FormPageActions extends AbstractFormPageActions
{
    /**
     * "Save Template" button
     *
     * @var string
     */
    protected $saveButton = "[data-ui-id='page-actions-toolbar-save-button']";

    /**
     * "Preview Template" button
     *
     * @var string
     */
    private $previewButton = "[data-role='template-preview']";

    /**
     * Click preview button on form page
     *
     * @return void
     */
    public function clickPreview()
    {
        $this->_rootElement->find($this->previewButton)->click();
    }
}
