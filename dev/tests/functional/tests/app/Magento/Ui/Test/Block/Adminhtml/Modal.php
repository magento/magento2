<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Press OK on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function acceptAlert()
    {
        $this->_rootElement->find($this->acceptButtonSelector)->click();
    }

    /**
     * Press Cancel on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function dismissAlert()
    {
        $this->_rootElement->find($this->dismissButtonSelector)->click();
    }

    /**
     * Press Close on an alert, confirm, prompt a dialog.
     *
     * @return void
     */
    public function closeAlert()
    {
        $this->_rootElement->find($this->closeButtonSelector)->click();
    }

    /**
     * Get the alert dialog text.
     *
     * @return string
     */
    public function getAlertText()
    {
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
        $this->_rootElement->find($this->inputFieldSelector)->setValue($text);
    }
}
