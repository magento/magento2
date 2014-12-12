<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Review\Test\Block\Adminhtml;

use Magento\Backend\Test\Block\FormPageActions as AbstractFormPageActions;

/**
 * Class FormPageActions
 * Page actions block of reviews edit page
 */
class FormPageActions extends AbstractFormPageActions
{
    /**
     * "Save Review" button
     *
     * @var string
     */
    protected $saveButton = '#save_button';
}
