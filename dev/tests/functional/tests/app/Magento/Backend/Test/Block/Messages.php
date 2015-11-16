<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Global messages block.
 */
class Messages extends Block
{
    /**
     * Success message selector.
     *
     * @var string
     */
    protected $successMessage = '[data-ui-id$=message-success]';

    /**
     * Last success message selector.
     *
     * @var string
     */
    protected $lastSuccessMessage = '[data-ui-id$=message-success]:last-child';

    /**
     * Message link.
     *
     * @var string
     */
    protected $messageLink = "//a[contains(.,'%s')]";

    /**
     * Error message selector.
     *
     * @var string
     */
    protected $errorMessage = '[data-ui-id$=message-error]';

    /**
     * Notice message selector.
     *
     * @var string
     */
    protected $noticeMessage = '[data-ui-id$=message-notice]';

    /**
     * Warning message selector.
     *
     * @var string
     */
    protected $warningMessage = '[data-ui-id$=message-warning]';

    /**
     * Wait for success message.
     *
     * @return bool
     */
    public function waitSuccessMessage()
    {
        return $this->waitForElementVisible($this->successMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Get all success messages which are present on the page.
     *
     * @return array
     */
    public function getSuccessMessages()
    {
        $this->waitForElementVisible($this->successMessage);
        $elements = $this->_rootElement->getElements($this->successMessage);

        $messages = [];
        foreach ($elements as $element) {
            $messages[] = $element->getText();
        }

        return $messages;
    }

    /**
     * Get last success message which is present on the page.
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        $this->waitForElementVisible($this->successMessage);

        return $this->_rootElement->find($this->lastSuccessMessage)->getText();
    }

    /**
     * Wait for element is visible in the page.
     *
     * @param string $selector
     * @param string $strategy
     * @return bool|null
     */
    public function waitForElementVisible($selector, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = $this->browser;
        return $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                $message = $browser->find($selector, $strategy);
                return $message->isVisible() ? true : null;
            }
        );
    }

    /**
     * Get all error message which is present on the page.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_rootElement
            ->find($this->errorMessage, Locator::SELECTOR_CSS)
            ->getText();
    }

    /**
     * Click on link in the message which is present on the page.
     *
     * @param string $messageType
     * @param string $linkText
     * @return void
     */
    public function clickLinkInMessage($messageType, $linkText)
    {
        if ($this->isVisibleMessage($messageType)) {
            $this->_rootElement
                ->find($this->{$messageType . 'Message'}, Locator::SELECTOR_CSS)
                ->find(sprintf($this->messageLink, $linkText), Locator::SELECTOR_XPATH)
                ->click();
        }
    }

    /**
     * Check is visible messages.
     *
     * @param string $messageType
     * @return bool
     */
    public function isVisibleMessage($messageType)
    {
        return $this->_rootElement
            ->find($this->{$messageType . 'Message'}, Locator::SELECTOR_CSS)
            ->isVisible();
    }

    /**
     * Check for error message.
     *
     * @return bool
     */
    public function assertErrorMessage()
    {
        return $this->waitForElementVisible($this->errorMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Check for notice message.
     *
     * @return bool
     */
    public function assertNoticeMessage()
    {
        return $this->waitForElementVisible($this->noticeMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Get notice message which is present on the page.
     *
     * @return string
     */
    public function getNoticeMessage()
    {
        $this->waitForElementVisible($this->noticeMessage);
        return $this->_rootElement->find($this->noticeMessage)->getText();
    }

    /**
     * Get warning message which is present on the page.
     *
     * @return string
     */
    public function getWarningMessage()
    {
        $this->waitForElementVisible($this->warningMessage);
        return $this->_rootElement->find($this->warningMessage)->getText();
    }
}
