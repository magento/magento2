<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Service;

use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;

/**
 * Class InvoiceService
 */
class InvoiceService implements InvoiceManagementInterface
{
    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $repository;

    /**
     * Repository
     *
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * Search Criteria Builder
     *
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * Filter Builder
     *
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * Invoice Notifier
     *
     * @var \Magento\Sales\Model\Order\InvoiceNotifier
     */
    protected $invoiceNotifier;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    protected $orderConverter;

    /**
     * Constructor
     *
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $repository
     * @param \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\Order\InvoiceNotifier $notifier
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order $orderConverter
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $repository,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\Order\InvoiceNotifier $notifier,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $orderConverter
    ) {
        $this->repository = $repository;
        $this->commentRepository = $commentRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->invoiceNotifier = $notifier;
        $this->orderRepository = $orderRepository;
        $this->orderConverter = $orderConverter;
    }

    /**
     * @inheritdoc
     */
    public function setCapture($id)
    {
        return (bool)$this->repository->get($id)->capture();
    }

    /**
     * @inheritdoc
     */
    public function getCommentsList($id)
    {
        $this->criteriaBuilder->addFilters(
            [$this->filterBuilder->setField('parent_id')->setValue($id)->setConditionType('eq')->create()]
        );
        $searchCriteria = $this->criteriaBuilder->create();
        return $this->commentRepository->getList($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function notify($id)
    {
        $invoice = $this->repository->get($id);
        return $this->invoiceNotifier->notify($invoice);
    }

    /**
     * @inheritdoc
     */
    public function setVoid($id)
    {
        return (bool)$this->repository->get($id)->void();
    }

    /**
     * @param Order $order
     * @param array $qtys
     * @return \Magento\Sales\Model\Order\Invoice
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareInvoice(Order $order, array $qtys = [])
    {
        $invoice = $this->orderConverter->toInvoice($order);
        $totalQty = 0;
        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->_canInvoiceItem($orderItem)) {
                continue;
            }
            $item = $this->orderConverter->itemToInvoiceItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (isset($qtys[$orderItem->getId()])) {
                $qty = (double) $qtys[$orderItem->getId()];
            } elseif (empty($qtys)) {
                $qty = $orderItem->getQtyToInvoice();
            } else {
                $qty = 0;
            }
            $totalQty += $qty;
            $this->setInvoiceItemQuantity($item, $qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }

    /**
     * Check if order item can be invoiced. Dummy item can be invoiced or with his children or
     * with parent item which is included to invoice
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _canInvoiceItem(\Magento\Sales\Api\Data\OrderItemInterface $item)
    {
        $qtys = [];
        if ($item->getLockedDoInvoice()) {
            return false;
        }
        if ($item->isDummy()) {
            if ($item->getHasChildren()) {
                foreach ($item->getChildrenItems() as $child) {
                    if (empty($qtys)) {
                        if ($child->getQtyToInvoice() > 0) {
                            return true;
                        }
                    } else {
                        if (isset($qtys[$child->getId()]) && $qtys[$child->getId()] > 0) {
                            return true;
                        }
                    }
                }
                return false;
            } elseif ($item->getParentItem()) {
                $parent = $item->getParentItem();
                if (empty($qtys)) {
                    return $parent->getQtyToInvoice() > 0;
                } else {
                    return isset($qtys[$parent->getId()]) && $qtys[$parent->getId()] > 0;
                }
            }
        } else {
            return $item->getQtyToInvoice() > 0;
        }
    }

    /**
     * Set quantity to invoice item
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemInterface $item
     * @param float $qty
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setInvoiceItemQuantity(\Magento\Sales\Api\Data\InvoiceItemInterface $item, $qty)
    {
        $qty = ($item->getOrderItem()->getIsQtyDecimal()) ? (double) $qty : (int) $qty;
        $qty = $qty > 0 ? $qty : 0;

        /**
         * Check qty availability
         */
        $qtyToInvoice = sprintf("%F", $item->getOrderItem()->getQtyToInvoice());
        $qty = sprintf("%F", $qty);
        if ($qty > $qtyToInvoice && !$item->getOrderItem()->isDummy()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid quantity to invoice item "%1".', $item->getName())
            );
        }

        $item->setQty($qty);

        return $this;
    }
}
