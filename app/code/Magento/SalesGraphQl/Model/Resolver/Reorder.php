<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\QuoteGraphQl\Model\Cart\CreateEmptyCartForCustomer;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Sales\Helper\Reorder as ReorderHelper;

/**
 * ReOrder customer order
 */
class Reorder implements ResolverInterface
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
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $currentUserId = $context->getUserId();
        $orderNumber = $args['orderNumber'];
        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, 1);

        if (!$order->getId()) {
            throw new GraphQlInputException(__('Cannot find order with number "%1"', $orderNumber));
        }
        if ($order->getCustomerId() != $currentUserId) {
            throw new GraphQlInputException(__('Order with number "%1" do not belong current customer', $orderNumber));
        }
        if (!$this->reorderHelper->canReorder($order->getId())) {
            throw new GraphQlInputException(__('Reorder is not available.'));
        }

        try {
            $cart = $this->cartManagement->getCartForCustomer($currentUserId);
        } catch (NoSuchEntityException $e) {
            $this->createEmptyCartForCustomer->execute($currentUserId, null);
            $cart =  $this->cartManagement->getCartForCustomer($currentUserId);
        }
        $cartModel = $this->cartFactory->create();
        $cartModel->setQuote($cart);

        $lineItemsErrors = [];
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cartModel->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->addLineItemError($lineItemsErrors, $item, $e->getMessage());
            } catch (\Throwable $e) {
                $this->logger->critical($e);
                $this->addLineItemError(
                    $lineItemsErrors,
                    $item,
                    __('We can\'t add this item to your shopping cart right now.') . $e->getMessage()
                );
            }
        }
        $cartModel->save();

        return [
            'cart' => [
                'model' => $cart,
            ],
            'errors' => $lineItemsErrors

        ];
    }

    /**
     * Add order line item error
     *
     * @param array $errors
     * @param \Magento\Sales\Model\Order\Item $item
     * @param string $message
     * @return void
     */
    private function addLineItemError(&$errors, \Magento\Sales\Model\Order\Item $item, string $message): void
    {
        $errors[] = [
            'sku' => $item->getSku() ?? ($item->getProduct() ? $item->getProduct()->getSku() : ''),
            'message' => $message,
        ];
    }
}
