<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Block\Adminhtml\Role;

use Magento\Backend\Test\Block\FormPageActions;

/**
 * Class PageActions
 * PageActions for the role edit page
 */
class PageActions extends FormPageActions
{
    /**
     * "Save Role" button
     *
     * @var string
     */
    protected $saveButton = '[data-ui-id="page-actions-toolbar-savebutton"]';

    /**
     * "Delete" button
     *
     * @var string
     */
    protected $deleteButton = '[data-ui-id="page-actions-toolbar-deletebutton"]';
}
