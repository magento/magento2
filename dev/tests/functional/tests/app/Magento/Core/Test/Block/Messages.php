<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Test\Block;

use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Global messages block
 *
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
     * Message link
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
     * Notice message selector
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
     * Check for success message
     *
     * @return bool
     */
    public function assertSuccessMessage()
    {
        return $this->waitForElementVisible($this->successMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Get all success messages which are present on the page
     *
     * @return string|array
     */
    public function getSuccessMessages()
    {
        $this->waitForElementVisible($this->successMessage);
        $elements = $this->_rootElement->find($this->successMessage)->getElements();

        $messages = [];
        foreach ($elements as $key => $element) {
            $messages[$key] = $element->getText();
        }

        return count($messages) > 1 ? $messages : $messages[0];
    }

    /**
     * Wait for element is visible in the page
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
     * Get all error messages which are present on the page
     *
     * @return string
     */
    public function getErrorMessages()
    {
        return $this->_rootElement
            ->find($this->errorMessage, Locator::SELECTOR_CSS)
            ->getText();
    }

    /**
     * Click on link in the messages which are present on the page
     *
     * @param string $messageType
     * @param string $linkText
     * @return void
     */
    public function clickLinkInMessages($messageType, $linkText)
    {
        if ($this->isVisibleMessage($messageType)) {
            $this->_rootElement
                ->find($this->{$messageType . 'Message'}, Locator::SELECTOR_CSS)
                ->find(sprintf($this->messageLink, $linkText), Locator::SELECTOR_XPATH)
                ->click();
        }
    }

    /**
     * Check is visible messages
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
     * Check for error message
     *
     * @return bool
     */
    public function assertErrorMessage()
    {
        return $this->waitForElementVisible($this->errorMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Check for notice message
     *
     * @return bool
     */
    public function assertNoticeMessage()
    {
        return $this->waitForElementVisible($this->noticeMessage, Locator::SELECTOR_CSS);
    }

    /**
     * Get notice message which is present on the page
     *
     * @return string
     */
    public function getNoticeMessages()
    {
        $this->waitForElementVisible($this->noticeMessage);
        return $this->_rootElement->find($this->noticeMessage)->getText();
    }

    /**
     * Get warning message which is present on the page
     *
     * @return string
     */
    public function getWarningMessages()
    {
        $this->waitForElementVisible($this->warningMessage);
        return $this->_rootElement->find($this->warningMessage)->getText();
    }
}
