<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit;

use Magento\Backend\Test\Block\FormPageActions;
use Magento\Mtf\Client\Locator;

/**
 * Category page actions.
 */
class PageActions extends FormPageActions
{
    /**
     * Top page element to implement a scrolling in case of floating blocks overlay.
     */
    const TOP_ELEMENT_TO_SCROLL = 'header.page-header';

    /**
     * Locator for "OK" button in warning block
     *
     * @var string
     */
    protected $warningBlock = '.ui-widget-content .ui-dialog-buttonset button:first-child';

    /**
     * Change Store View selector.
     *
     * @var string
     */
    protected $storeChangeButton = '#store-change-button';

    /**
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

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

    /**
     * Select Store View.
     *
     * @param string $name
     * @return void
     */
    public function selectStoreView($name)
    {
        $this->browser->find(self::TOP_ELEMENT_TO_SCROLL)->hover();
        $this->_rootElement->find($this->storeChangeButton)->click();
        $this->waitForElementVisible($name, Locator::SELECTOR_LINK_TEXT);
        $this->_rootElement->find($name, Locator::SELECTOR_LINK_TEXT)->click();
        $element = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(\Magento\Ui\Test\Block\Adminhtml\Modal::class, ['element' => $element]);
        $modal->acceptAlert();
        $this->waitForElementVisible($this->storeChangeButton);
    }
}
