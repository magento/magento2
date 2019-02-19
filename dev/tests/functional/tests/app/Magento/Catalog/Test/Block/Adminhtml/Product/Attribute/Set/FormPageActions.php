<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
