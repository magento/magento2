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
     * Get comment history block data.
     *
     * @return array
     */
    public function getComments()
    {
        $result = [];
        $elements = $this->_rootElement->getElements($this->commentHistory);
        foreach ($elements as $key => $item) {
            $result[$key]['date'] = $item->find($this->commentHistoryDate)->getText();
            $result[$key]['time'] = $item->find($this->commentHistoryTime)->getText();
            $result[$key]['status'] = $item->find($this->commentHistoryStatus)->getText();
            $result[$key]['is_customer_notified'] = $item->find($this->commentHistoryNotifiedStatus)->getText();
            $result[$key]['comment'] = $item->find($this->comment)->getText();
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
