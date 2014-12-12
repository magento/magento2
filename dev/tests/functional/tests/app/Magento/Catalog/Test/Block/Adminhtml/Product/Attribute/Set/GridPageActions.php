<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\Set;

use Magento\Backend\Test\Block\GridPageActions as AbstractGridPageActions;

/**
 * Class GridPageActions
 * Grid page actions block on Product Templates page
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
