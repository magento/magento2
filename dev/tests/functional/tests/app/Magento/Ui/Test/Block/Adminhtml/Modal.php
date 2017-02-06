<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Block\Adminhtml;

use Magento\Mtf\Block\Block;

/**
 * Alert, confirm, prompt block.
 */
class Modal extends Block
{
    /**
     * Locator value for accept button.
     *
     * @var string
     */
    protected $acceptButtonSelector = '.action-accept';

    /**
     * Locator value for dismiss button.
     *
     * @var string
     */
    protected $dismissButtonSelector = '.action-dismiss';

    /**
     * Locator value for close button.
     *
     * @var string
     */
    protected $closeButtonSelector = '.action-close';

    /**
     * Locator value for prompt input.
     *
     * @var string
     */
    protected $inputFieldSelector = '[data-role="promptField"]';

    /**
     * Locator value for accept warning button.
     *
     * @var string
     */
    protected $acceptWarningSelector = '.action-primary';

    /**
     * Locator value for decline warning button.
     *
     * @var string
     */
    protected $dismissWarningSelector = '.action-secondary';

    /**
     * Modal overlay selector.
     *
     * @var string
     */
    protected $modalOverlay = '.modals-overlay';

    /**
     * Selector for spinner element.
     *
     * @var string
     */
    protected $loadingMask = '[data-role="loader"]';

    /**
     * Press OK on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function acceptAlert()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->acceptButtonSelector)->click();
    }

    /**
     * Press OK on a warning popup.
     *
     * @return void
     */
    public function acceptWarning()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->acceptWarningSelector)->click();
        $this->waitForElementNotVisible($this->loadingMask);
    }

    /**
     * Press Cancel on a warning popup.
     *
     * @return void
     */
    public function dismissWarning()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->dismissWarningSelector)->click();
    }

    /**
     * Press Cancel on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function dismissAlert()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->dismissButtonSelector)->click();
    }

    /**
     * Press Close on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function closeAlert()
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->closeButtonSelector)->click();
    }

    /**
     * Get the alert dialog text.
     *
     * @return string
     */
    public function getAlertText()
    {
        $this->waitModalAnimationFinished();
        return $this->_rootElement->find($this->inputFieldSelector)->getValue();
    }

    /**
     * Set the text to a prompt popup.
     *
     * @param string $text
     * @return void
     */
    public function setAlertText($text)
    {
        $this->waitModalAnimationFinished();
        $this->_rootElement->find($this->inputFieldSelector)->setValue($text);
    }

    /**
     * Wait until modal window will disapper.
     *
     * @return void
     */
    public function waitModalWindowToDisappear()
    {
        $this->browser->waitUntil(
            function () {
                return $this->browser->find($this->modalOverlay)->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Waiting until CSS animation is done.
     * Transition-duration is set at this file: "<magento_root>/lib/web/css/source/components/_modals.less"
     *
     * @return void
     */
    private function waitModalAnimationFinished()
    {
        usleep(500000);
    }
}
