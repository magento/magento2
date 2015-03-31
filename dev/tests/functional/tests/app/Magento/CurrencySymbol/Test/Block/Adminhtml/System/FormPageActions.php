<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CurrencySymbol\Test\Block\Adminhtml\System;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Class FormPageActions
 * Form Page Actions for Currency Symbol
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Save" button
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id="page-actions-toolbar-save-button"]';
}
