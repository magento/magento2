<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Phrase;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Updates case order comments history.
 * @since 2.2.0
 */
class CommentsHistoryUpdater
{
    /**
     * @var HistoryFactory
     * @since 2.2.0
     */
    private $historyFactory;

    /**
     * CommentsHistoryUpdater constructor.
     *
     * @param HistoryFactory $historyFactory
     * @since 2.2.0
     */
    public function __construct(HistoryFactory $historyFactory)
    {
        $this->historyFactory = $historyFactory;
    }

    /**
     * Adds comment to case related order.
     * Throws an exception if cannot save history comment.
     *
     * @param CaseInterface $case
     * @param Phrase $message
     * @param string $status
     * @return void
     * @since 2.2.0
     */
    public function addComment(CaseInterface $case, Phrase $message, $status = '')
    {
        if (!$message->getText()) {
            return;
        }

        /** @var \Magento\Sales\Api\Data\OrderStatusHistoryInterface $history */
        $history = $this->historyFactory->create();
        $history->setParentId($case->getOrderId())
            ->setComment($message)
            ->setEntityName('order')
            ->setStatus($status)
            ->save();
    }
}
