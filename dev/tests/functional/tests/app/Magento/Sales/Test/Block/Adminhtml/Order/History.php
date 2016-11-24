<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Order comments block.
 */
class History extends Block
{
    /**
     * Comment history Id.
     *
     * @var string
     */
    protected $commentHistory = '.note-list-comment';

    /**
     * Comment history status.
     *
     * @var string
     */
    protected $commentHistoryStatus = '.note-list-status';

    /**
     * Comment history notified status.
     *
     * @var string
     */
    protected $commentHistoryNotifiedStatus = '.note-list-customer';

    /**
     * Authorized Amount.
     *
     * @var string
     */
    protected $authorizedAmount = '//div[@class="note-list-comment"][contains(text(), "Authorized amount of")]';

    /**
     * Captured Amount from IPN.
     *
     * @var string
     */
    protected $capturedAmount = '//div[@class="note-list-comment"][contains(text(), "Captured amount of")]';

    /**
     * Refunded Amount.
     *
     * @var string
     */
    protected $refundedAmount = '//div[@class="note-list-comment"][contains(text(), "We refunded")]';

    /**
     * Voided Amount.
     *
     * @var string
     */
    protected $voidedAmount = '//div[@class="note-list-comment"][contains(text(), "Voided authorization")]';

    /**
     * Note list locator.
     *
     * @var string
     */
    protected $noteList = '.note-list';

    /**
     * Get comments history.
     *
     * @return string
     */
    public function getCommentsHistory()
    {
        $this->waitCommentsHistory();
        return $this->_rootElement->find($this->commentHistory, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Get the authorized amount from the comments history.
     *
     * @return string
     */
    public function getAuthorizedAmount()
    {
        $this->waitCommentsHistory();
        return $this->_rootElement->find($this->authorizedAmount, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Get the captured amount from the comments history.
     *
     * @return array
     */
    public function getCapturedAmount()
    {
        $result = [];
        $this->waitCommentsHistory();
        $captureComments = $this->_rootElement->getElements($this->capturedAmount, Locator::SELECTOR_XPATH);
        foreach ($captureComments as $captureComment) {
            $result[] = $captureComment->getText();
        }
        return $result;
    }

    /**
     * Get the refunded amount from the comments history.
     *
     * @return array
     */
    public function getRefundedAmount()
    {
        $result = [];
        $this->waitCommentsHistory();
        $refundedComments = $this->_rootElement->getElements($this->refundedAmount, Locator::SELECTOR_XPATH);
        foreach ($refundedComments as $refundedComment) {
            $result[] = $refundedComment->getText();
        }
        return $result;
    }

    /**
     * Get the voided amount from the comments history.
     *
     * @return string
     */
    public function getVoidedAmount()
    {
        $this->waitCommentsHistory();
        return $this->_rootElement->find($this->voidedAmount, Locator::SELECTOR_XPATH)->getText();
    }

    /**
     * Gets the status which presented in comment
     *
     * @return string
     */
    public function getStatus()
    {
        $this->waitCommentsHistory();
        return $this->_rootElement->find($this->commentHistoryStatus, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Gets the is customer notified status which presented in comment
     *
     * @return string
     */
    public function getNotifiedStatus()
    {
        $this->waitCommentsHistory();
        return $this->_rootElement->find($this->commentHistoryNotifiedStatus, Locator::SELECTOR_CSS)->getText();
    }

    /**
     * Wait for comments history is visible.
     *
     * @return void
     */
    protected function waitCommentsHistory()
    {
        $element = $this->_rootElement;
        $selector = $this->noteList;
        $element->waitUntil(
            function () use ($element, $selector) {
                return $element->find($selector)->isVisible() ? true : null;
            }
        );
    }
}
