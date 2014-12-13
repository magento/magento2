<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
