<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

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
     * @var \Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory
     */
    private $commentFactory;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * CreditmemoDocumentFactory constructor.
     *
     * @param \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory
     * @param \Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory $commentFactory
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
        \Magento\Sales\Api\Data\CreditmemoCommentInterfaceFactory $commentFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->creditmemoFactory = $creditmemoFactory;
        $this->commentFactory = $commentFactory;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get array with original data for new Creditmemo document
     *
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return array
     */
    private function getCreditmemoCreationData(
        array $items = [],
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $data = ['qtys' => []];
        foreach ($items as $item) {
            $data['qtys'][$item->getOrderItemId()] = $item->getQty();
        }
        if ($arguments) {

            $data = array_merge(
                [
                    'shipping_amount' => $arguments->getShippingAmount(),
                    'adjustment_positive' => $arguments->getAdjustmentPositive(),
                    'adjustment_negative' => $arguments->getAdjustmentNegative(),
                ],
                $data
            );
        }
        return $data;
    }

    /**
     *  Attach comment to the Creditmemo document.
     *
     * @param \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment
     * @param bool $appendComment
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    private function attachComment(
        \Magento\Sales\Api\Data\CreditmemoInterface $creditmemo,
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment,
        $appendComment = false
    ) {
        $commentData = [
            'comment' => $comment->getComment(),
            'is_visible_on_front' => $comment->getIsVisibleOnFront()
        ];
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
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function createFromOrder(
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $items = [],
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
    ) {
        $data = $this->getCreditmemoCreationData($items, $arguments);
        $creditmemo = $this->creditmemoFactory->createByOrder($order, $data);
        if ($comment) {
            $creditmemo = $this->attachComment($creditmemo, $comment, $appendComment);
        }
        return $creditmemo;
    }

    /**
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @param \Magento\Sales\Api\Data\CreditmemoItemCreationInterface[] $items
     * @param \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface|null $comment
     * @param bool|null $appendComment
     * @param \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface|null $arguments
     * @return \Magento\Sales\Api\Data\CreditmemoInterface
     */
    public function createFromInvoice(
        \Magento\Sales\Api\Data\InvoiceInterface $invoice,
        array $items = [],
        \Magento\Sales\Api\Data\CreditmemoCommentCreationInterface $comment = null,
        $appendComment = false,
        \Magento\Sales\Api\Data\CreditmemoCreationArgumentsInterface $arguments = null
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
