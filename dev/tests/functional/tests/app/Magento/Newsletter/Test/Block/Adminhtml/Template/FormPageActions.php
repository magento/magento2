<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * "Save" button
     *
     * @var string
     */
    protected $saveButton = "[data-ui-id='page-actions-toolbar-save-button']";
}
