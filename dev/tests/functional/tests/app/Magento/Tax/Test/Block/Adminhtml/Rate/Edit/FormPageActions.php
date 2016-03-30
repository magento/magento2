<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rate\Edit;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Class FormPageActions
 * Form page actions block in Tax Rate new/edit page
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Save Rate" button
     *
     * @var string
     */
    protected $saveButton = '.save-rate';

    /**
     * "Delete" button
     *
     * @var string
     */
    protected $deleteButton = '.delete';
}
