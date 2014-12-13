<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
