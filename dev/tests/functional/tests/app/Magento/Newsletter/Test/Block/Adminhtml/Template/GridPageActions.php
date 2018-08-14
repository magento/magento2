<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Block\Adminhtml\Template;

use Magento\Backend\Test\Block\GridPageActions as AbstractGridPageActions;

/**
 * Class GridPageActions
 * Grid page actions block
 *
 * @package Magento\Newsletter\Test\Block\Adminhtml\Template
 */
class GridPageActions extends AbstractGridPageActions
{
    /**
     * "Add New" button
     *
     * @var string
     */
    protected $addNewButton = "[data-ui-id='page-actions-toolbar-add-button']";
}
