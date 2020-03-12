<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Exception\InputException;
use Magento\Sales\Api\ReorderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Helper\Reorder as ReorderHelper;

/**
 * @inheritdoc
 */
class Reorder implements ReorderInterface
{
    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    private $cartFactory;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var ReorderHelper
     */
    private $reorderHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CreateEmptyCartForCustomer
     */
    private $createEmptyCartForCustomer;

    /**
     * @param OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param ReorderHelper $reorderHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     */
    public function __construct(
        OrderFactory $orderFactory,
        \Magento\Checkout\Model\CartFactory $cartFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        ReorderHelper $reorderHelper,
        \Psr\Log\LoggerInterface $logger,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer
    ) {
        $this->orderFactory = $orderFactory;
        $this->cartFactory = $cartFactory;
        $this->cartManagement = $cartManagement;
        $this->reorderHelper = $reorderHelper;
        $this->logger = $logger;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
    }

    /**
     * @inheritdoc
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(string $incrementOrderId, string $storeId): \Magento\Sales\Api\Data\Reorder\ReorderOutput
    {
        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($incrementOrderId, $storeId);

        if (!$order->getId()) {
            throw new NoSuchEntityException(
                __('Cannot find order with number "%1" in store "%2"', $incrementOrderId, $storeId)
            );
        }
        if (!$this->reorderHelper->canReorder($order->getId())) {
            throw new InputException(__('Reorder is not available.'));
        }

        $customerId = $order->getCustomerId();

        try {
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId);
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        }
        $cartModel = $this->cartFactory->create();
        $cartModel->setQuote($cart);

        $lineItemsErrors = [];
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $this->addOrderItem($cartModel, $item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->addLineItemError($lineItemsErrors, $item, $e->getMessage());
            } catch (\Throwable $e) {
                $this->logger->critical($e);
                $this->addLineItemError(
                    $lineItemsErrors,
                    $item,
                    __('We can\'t add this item to your shopping cart right now.')
                );
            }
        }
        $cartModel->save();

        return new \Magento\Sales\Api\Data\Reorder\ReorderOutput($cart, $lineItemsErrors);
    }


    /**
     * Convert order item to quote item
     *
     * @param \Magento\Checkout\Model\Cart $cartModel
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addOrderItem($cartModel, $orderItem): void
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            $info->setQty($orderItem->getQtyOrdered());

            $cartModel->addProduct($orderItem->getProductId(), $info);
        }
    }

    /**
     * Add order line item error
     *
     * @param array $errors
     * @param \Magento\Sales\Model\Order\Item $item
     * @param string $message
     * @return void
     */
    private function addLineItemError(&$errors, \Magento\Sales\Model\Order\Item $item, $message): void
    {
        $errors[] = new \Magento\Sales\Api\Data\Reorder\LineItemError(
            $item->getProduct() ? $item->getProduct()->getSku() : $item->getSku() ?? '',
            $message
        );
    }
}
