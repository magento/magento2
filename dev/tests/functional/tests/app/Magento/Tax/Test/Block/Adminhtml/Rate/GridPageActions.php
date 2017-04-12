<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Block\Adminhtml\Rate;

use Magento\Backend\Test\Block\GridPageActions as ParentGridPageActions;

/**
 * Class GridPageActions
 * Grid page actions block in Tax Rate grid page
 */
class GridPageActions extends ParentGridPageActions
{
    /**
     * "Add New Tax Rate" button
     *
     * @var string
     */
    protected $addNewButton = '.add-tax-rate';
}
