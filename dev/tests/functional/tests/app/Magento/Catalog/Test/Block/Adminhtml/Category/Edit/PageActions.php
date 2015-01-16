<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Magento\Backend\Test\Block\FormPageActions;

/**
 * Class PageActions
 * Category page actions
 */
class PageActions extends FormPageActions
{
    /**
     * Locator for "OK" button in warning block
     *
     * @var string
     */
    protected $warningBlock = '.ui-widget-content .ui-dialog-buttonset button:first-child';

    /**
     * Click on "Save" button
     *
     * @return void
     */
    public function save()
    {
        parent::save();
        $warningBlock = $this->browser->find($this->warningBlock);
        if ($warningBlock->isVisible()) {
            $warningBlock->click();
        }
    }
}
