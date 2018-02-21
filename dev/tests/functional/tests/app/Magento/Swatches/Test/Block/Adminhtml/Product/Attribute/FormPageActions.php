<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Test\Block\Adminhtml\Product\Attribute;

/**
 * "Page Actions" block on "create/update Attribute" Admin panel page.
 */
class FormPageActions extends \Magento\Backend\Test\Block\FormPageActions
{
    /**
     * Locator of "Save and Continue Edit" form button.
     *
     * @var string
     */
    protected $saveAndContinueButton = '#save_and_edit_button';
}
