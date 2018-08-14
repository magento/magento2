<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Test\Block\Adminhtml\Promo;

use Magento\Backend\Test\Block\GridPageActions as AbstractPageActions;

/**
 * Class GridPageActions
 * Grid page actions block for 'Catalog Price Rules'
 *
 */
class GridPageActions extends AbstractPageActions
{
    /**
     * 'Apply Rules' button
     *
     * @var string
     */
    protected $applyRules = '#apply_rules';

    /**
     * Click 'Apply Rules' button
     */
    public function applyRules()
    {
        $this->_rootElement->find($this->applyRules)->click();
    }
}
