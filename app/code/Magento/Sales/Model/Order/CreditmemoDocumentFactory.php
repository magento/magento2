<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCommentCreationInterface;
use Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory;
use Magento\Framework\EntityManager\HydratorPool;
use Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class CreditmemoDocumentFactory
 */
class CreditmemoDocumentFactory
{

    /**
     * @var \Magento\Sales\Model\Order\CreditmemoFactory
     */
    private $creditmemoFactory;

    /**
     * @var CreditmemoCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var HydratorPool
     */
    private $hydratorPool;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CreditmemoDocumentFactory constructor.
     *
     * @param CreditmemoFactory $creditmemoFactory
     * @param CreditmemoCommentInterfaceFactory $commentFactory
     * @param HydratorPool $hydratorPool
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CreditmemoFactory $creditmemoFactory,
        CreditmemoCommentInterfaceFactory $commentFactory,
        HydratorPool $hydratorPool,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->commentFactory = $commentFactory;
        $this->hydratorPool = $hydratorPool;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get array with original data for new Creditmemo document
     *
     * @param CreditmemoItemCreationInterface[] $items
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return array
     */
    private function getCreditmemoCreationData(
        array $items = [],
        CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $data = ['qtys' => []];
        foreach ($items as $item) {
            $data['qtys'][$item->getOrderItemId()] = $item->getQty();
        }
        if ($arguments) {
            $hydrator = $this->hydratorPool->getHydrator(CreditmemoCreationArgumentsInterface::class);
            $data = array_merge($hydrator->extract($arguments), $data);
        }
        return $data;
    }

    /**
     *  Attach comment to the Creditmemo document.
     *
     * @param CreditmemoInterface $creditmemo
     * @param CreditmemoCommentCreationInterface $comment
     * @param bool $appendComment
     * @return CreditmemoInterface
     */
    private function attachComment(CreditmemoInterface $creditmemo, CreditmemoCommentCreationInterface $comment, $appendComment = false)
    {
        $commentData = $this->hydratorPool->getHydrator(CreditmemoCommentCreationInterface::class)
            ->extract($comment);
        $comment = $this->commentFactory->create(['data' => $commentData]);
        $comment->setParentId($creditmemo->getEntityId())
            ->setStoreId($creditmemo->getStoreId())
            ->setCreditmemo($creditmemo)
            ->setIsCustomerNotified($appendComment);
        $creditmemo->setComments([$comment]);
        return $creditmemo;

    }

    /**
     * Create new Creditmemo
     * @param OrderInterface $order
     * @param CreditmemoItemCreationInterface[] $items
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param bool|null $appendComment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return CreditmemoInterface
     */
    public function createFromOrder(
        OrderInterface $order,
        array $items = [],
        CreditmemoCommentCreationInterface $comment = null,
        $appendComment = false,
        CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $data = $this->getCreditmemoCreationData($items, $arguments);
        $creditmemo = $this->creditmemoFactory->createByOrder($order, $data);
        if ($comment) {
            $creditmemo = $this->attachComment($creditmemo, $comment, $appendComment);
        }
        return $creditmemo;
    }

    /**
     * @param InvoiceInterface $invoice
     * @param OrderInterface $order
     * @param CreditmemoItemCreationInterface[] $items
     * @param CreditmemoCommentCreationInterface|null $comment
     * @param bool|null $appendComment
     * @param CreditmemoCreationArgumentsInterface|null $arguments
     * @return CreditmemoInterface
     */
    public function createFromInvoice(
        InvoiceInterface $invoice,
        array $items = [],
        CreditmemoCommentCreationInterface $comment = null,
        $appendComment = false,
        CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $data = $this->getCreditmemoCreationData($items, $arguments);
        /** @var $invoice \Magento\Sales\Model\Order\Invoice */
        $invoice->setOrder($this->orderRepository->get($invoice->getOrderId()));
        $creditmemo = $this->creditmemoFactory->createByInvoice($invoice, $data);
        if ($comment) {
            $creditmemo = $this->attachComment($creditmemo, $comment, $appendComment);
        }
        return $creditmemo;
    }
}
