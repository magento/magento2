<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
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
     * @var CartManagementInterface
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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param OrderFactory $orderFactory
     * @param CartManagementInterface $cartManagement
     * @param ReorderHelper $reorderHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param CreateEmptyCartForCustomer $createEmptyCartForCustomer
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        OrderFactory $orderFactory,
        CartManagementInterface $cartManagement,
        CreateEmptyCartForCustomer $createEmptyCartForCustomer,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        ReorderHelper $reorderHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->cartManagement = $cartManagement;
        $this->createEmptyCartForCustomer = $createEmptyCartForCustomer;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->reorderHelper = $reorderHelper;
        $this->logger = $logger;
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
            /** @var \Magento\Quote\Model\Quote $cart */
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($customerId);
            $cart = $this->cartManagement->getCartForCustomer($customerId);
        }

        $lineItemsErrors = [];
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $this->addOrderItem($cart, $item);
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

        $this->cartRepository->save($cart);

        return new \Magento\Sales\Api\Data\Reorder\ReorderOutput($cart, $lineItemsErrors);
    }


    /**
     * Convert order item to quote item
     *
     * @param \Magento\Quote\Model\Quote $cart
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addOrderItem(\Magento\Quote\Model\Quote $cart, $orderItem): void
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            $info->setQty($orderItem->getQtyOrdered());

            try {
                $product = $this->productRepository->getById($orderItem->getProductId(), false, null, true);
            } catch (NoSuchEntityException $e) {
                throw new LocalizedException(__('Could not find a product with ID "%1"', $orderItem->getProductId()));
            }
            $cart->addProduct($product, $info);
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
