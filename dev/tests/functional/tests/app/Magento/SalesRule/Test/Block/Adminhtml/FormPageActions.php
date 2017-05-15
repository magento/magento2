<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\FormPageActions as BackendFormPageActions;

/**
 * @inheritdoc
 */
class FormPageActions extends BackendFormPageActions
{
    /**
     * "Save and Continue Edit" button.
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save_and_continue_edit';
}
