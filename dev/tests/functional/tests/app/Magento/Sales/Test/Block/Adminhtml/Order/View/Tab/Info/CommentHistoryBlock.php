<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info;

use Magento\Mtf\Block\Block;

/**
 * Order comments history block.
 */
class CommentHistoryBlock extends Block
{
    /**
     * Comment history list locator.
     *
     * @var string
     */
    protected $commentHistory = '.note-list';

    /**
     * Comment date.
     *
     * @var string
     */
    protected $commentHistoryDate = '.note-list-date';

    /**
     * Comment time.
     *
     * @var string
     */
    protected $commentHistoryTime = '.note-list-time';

    /**
     * Comment status.
     *
     * @var string
     */
    protected $commentHistoryStatus = '.note-list-status';

    /**
     * Comment notified status.
     *
     * @var string
     */
    protected $commentHistoryNotifiedStatus = '.note-list-customer';

    /**
     * Comment locator.
     *
     * @var string
     */
    protected $comment = '.note-list-comment';

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
     * Get comment history block data.
     *
     * @return array
     */
    public function getComments()
    {
        $result = [];
        $elements = $this->_rootElement->getElements($this->commentHistory);
        foreach ($elements as $item) {
            $result['date'] = $item->find($this->commentHistoryDate)->getText();
            $result['time'] = $item->find($this->commentHistoryTime)->getText();
            $result['status'] = $item->find($this->commentHistoryStatus)->getText();
            $result['is_customer_notified'] = $item->find($this->commentHistoryNotifiedStatus)->getText();
            $result['authorized_amount'] = $item->find($this->authorizedAmount)->getText();
            $result['captured_amount'] = $item->find($this->capturedAmount)->getText();
            $result['refunded_amount'] = $item->find($this->refundedAmount)->getText();
            $result['voided_amount'] = $item->find($this->voidedAmount)->getText();
        }

        return $result;
    }

    /**
     * Get last comment.
     *
     * @return array
     */
    public function getLatestComment()
    {
        $comments = $this->getComments();
        return end($comments);
    }
}
