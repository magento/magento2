<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Set;

use Magento\Backend\Test\Block\FormPageActions as AbstractFormPageActions;

/**
 * Class FormPageActions
 * Form page actions in Attribute Set page
 */
class FormPageActions extends AbstractFormPageActions
{
    /**
     * "Save" button
     *
     * @var string
     */
    protected $saveButton = '.save-attribute-set';

    /**
     * "Delete" button
     *
     * @var string
     */
    protected $deleteButton = '.delete';
}
