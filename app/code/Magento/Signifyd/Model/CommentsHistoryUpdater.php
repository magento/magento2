<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\Phrase;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Updates case order comments history.
 */
class CommentsHistoryUpdater
{
    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * CommentsHistoryUpdater constructor.
     *
     * @param HistoryFactory $historyFactory
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
     * @return void
     * @throws \Exception
     */
    public function addComment(CaseInterface $case, Phrase $message)
    {
        if (!$message->getText()) {
            return;
        }

        /** @var \Magento\Sales\Api\Data\OrderStatusHistoryInterface $history */
        $history = $this->historyFactory->create();
        $history->setParentId($case->getOrderId())
            ->setComment($message)
            ->setEntityName('order')
            ->save();
    }
}
