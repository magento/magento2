<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Set;

use Magento\Backend\Test\Block\GridPageActions as AbstractGridPageActions;

/**
 * Class GridPageActions
 * Grid page actions block on Attribute Sets page
 */
class GridPageActions extends AbstractGridPageActions
{
    /**
     * "Add New" button
     *
     * @var string
     */
    protected $addNewButton = '[data-ui-id="page-actions-toolbar-addbutton"]';
}
