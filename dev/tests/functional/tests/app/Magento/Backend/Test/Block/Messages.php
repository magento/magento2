<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

use Magento\Mtf\Client\Locator;

/**
 * Backend Messages block.
 */
class Messages extends \Magento\Ui\Test\Block\Messages
{
    /**
     * Message link.
     *
     * @var string
     */
    protected $messageLink = "//a[contains(.,'%s')]";

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
}
