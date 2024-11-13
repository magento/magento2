<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;

/**
 * Class InvoiceService
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceService implements InvoiceManagementInterface
{
    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Magento\Sales\Api\InvoiceCommentRepositoryInterface
     */
    protected $commentRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
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
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @param \Magento\Sales\Api\InvoiceRepositoryInterface $repository
     * @param \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Sales\Model\Order\InvoiceNotifier $notifier
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\Convert\Order $orderConverter
     * @param JsonSerializer $serializer
     */
    public function __construct(
        \Magento\Sales\Api\InvoiceRepositoryInterface $repository,
        \Magento\Sales\Api\InvoiceCommentRepositoryInterface $commentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Sales\Model\Order\InvoiceNotifier $notifier,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $orderConverter,
        JsonSerializer $serializer
    ) {
        $this->repository = $repository;
        $this->commentRepository = $commentRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->invoiceNotifier = $notifier;
        $this->orderRepository = $orderRepository;
        $this->orderConverter = $orderConverter;
        $this->serializer = $serializer;
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
     * Creates an invoice based on the order and quantities provided.
     *
     * Explanation for `if` statements:
     * - using qty defined in `$preparedItemsQty` is prioritized
     * - if qty is not defined and item is dummy, get ordered qty
     * - if qty is not defined, get qty to invoice
     * - else qty is 0
     *
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return Invoice
     * @throws LocalizedException
     * @throws \Exception
     */
    public function prepareInvoice(
        Order $order,
        array $orderItemsQtyToInvoice = []
    ): InvoiceInterface {
        $totalQty = 0;
        $invoice = $this->orderConverter->toInvoice($order);
        $preparedItemsQty = $this->prepareItemsQty($order, $orderItemsQtyToInvoice);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$this->canInvoiceItem($orderItem, $preparedItemsQty)) {
                continue;
            }

            if (isset($preparedItemsQty[$orderItem->getId()])) {
                $qty = $preparedItemsQty[$orderItem->getId()];
            } elseif ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } elseif (empty($orderItemsQtyToInvoice)) {
                $qty = $orderItem->getQtyToInvoice();
            } else {
                $qty = 0;
            }

            $invoiceItem = $this->orderConverter->itemToInvoiceItem($orderItem);
            $this->setInvoiceItemQuantity($invoiceItem, (float) $qty);
            $invoice->addItem($invoiceItem);
            $totalQty += $qty;
        }

        $invoice->setTotalQty($totalQty);
        $invoice->collectTotals();
        $order->getInvoiceCollection()->addItem($invoice);

        return $invoice;
    }

    /**
     * Prepare qty to invoice for parent and child products if theirs qty is not specified in initial request.
     *
     * @param Order $order
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    private function prepareItemsQty(
        Order $order,
        array $orderItemsQtyToInvoice
    ): array {
        foreach ($order->getAllItems() as $orderItem) {
            if (isset($orderItemsQtyToInvoice[$orderItem->getId()])) {
                if ($orderItem->getHasChildren()) {
                    $orderItemsQtyToInvoice = $this->setChildItemsQtyToInvoice($orderItem, $orderItemsQtyToInvoice);
                }
            } else {
                if (isset($orderItemsQtyToInvoice[$orderItem->getParentItemId()])) {
                    $orderItemsQtyToInvoice[$orderItem->getId()] =
                        $orderItemsQtyToInvoice[$orderItem->getParentItemId()];
                }
            }
        }

        return $orderItemsQtyToInvoice;
    }

    /**
     * Sets qty to invoice for children order items, if not set.
     *
     * @param OrderItemInterface $parentOrderItem
     * @param array $orderItemsQtyToInvoice
     * @return array
     */
    private function setChildItemsQtyToInvoice(
        OrderItemInterface $parentOrderItem,
        array $orderItemsQtyToInvoice
    ): array {
        /** @var OrderItemInterface $childOrderItem */
        foreach ($parentOrderItem->getChildrenItems() as $childOrderItem) {
            if (!isset($orderItemsQtyToInvoice[$childOrderItem->getItemId()])) {
                $productOptions = $childOrderItem->getProductOptions();

                if (isset($productOptions['bundle_selection_attributes'])) {
                    $bundleSelectionAttributes = $this->serializer
                        ->unserialize($productOptions['bundle_selection_attributes']);
                    $orderItemsQtyToInvoice[$childOrderItem->getItemId()] =
                        $bundleSelectionAttributes['qty'] * $orderItemsQtyToInvoice[$parentOrderItem->getItemId()];
                }
            }
        }

        return $orderItemsQtyToInvoice;
    }

    /**
     * Check if order item can be invoiced.
     *
     * @param OrderItemInterface $item
     * @param array $qtys
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function canInvoiceItem(OrderItemInterface $item, array $qtys): bool
    {
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
     * Set quantity to invoice item.
     *
     * @param InvoiceItemInterface $item
     * @param float $qty
     * @return InvoiceManagementInterface
     * @throws LocalizedException
     */
    private function setInvoiceItemQuantity(InvoiceItemInterface $item, float $qty): InvoiceManagementInterface
    {
        $qty = ($item->getOrderItem()->getIsQtyDecimal()) ? (double) $qty : (int) $qty;
        $qty = $qty > 0 ? $qty : 0;

        /**
         * Check qty availability
         */
        $qtyToInvoice = sprintf("%F", $item->getOrderItem()->getQtyToInvoice());
        $qty = sprintf("%F", $qty);
        if ($qty > $qtyToInvoice && !$item->getOrderItem()->isDummy()) {
            throw new LocalizedException(
                __('We found an invalid quantity to invoice item "%1".', $item->getName())
            );
        }

        $item->setQty($qty);

        return $this;
    }
}
