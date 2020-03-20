<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Sales\Model\Reorder;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;

/**
 * Allows customer quickly to reorder previously added products and put them to the Cart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Reorder
{
    /**#@+
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    private const ERROR_INSUFFICIENT_STOCK = 'INSUFFICIENT_STOCK';
    private const ERROR_NOT_SALABLE = 'NOT_SALABLE';
    private const ERROR_REORDER_NOT_AVAILABLE = 'REORDER_NOT_AVAILABLE';
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * List of error messages and codes.
     */
    private const MESSAGE_CODES = [
        'Product that you are trying to add is not available' => self::ERROR_NOT_SALABLE,
        'This product is out of stock' => self::ERROR_NOT_SALABLE,
        'The fewest you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The most you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The requested qty is not available' => self::ERROR_INSUFFICIENT_STOCK,
    ];

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var ReorderHelper
     */
    private $reorderHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Data\Error[]
     */
    private $errors = [];

    /**
     * @var CustomerCartResolver
     */
    private $customerCartProvider;

    /**
     * @param OrderFactory $orderFactory
     * @param CustomerCartResolver $customerCartProvider
     * @param CartRepositoryInterface $cartRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ReorderHelper $reorderHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        OrderFactory $orderFactory,
        CustomerCartResolver $customerCartProvider,
        CartRepositoryInterface $cartRepository,
        ProductRepositoryInterface $productRepository,
        ReorderHelper $reorderHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderFactory = $orderFactory;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->reorderHelper = $reorderHelper;
        $this->logger = $logger;
        $this->customerCartProvider = $customerCartProvider;
    }

    /**
     * Allows customer quickly to reorder previously added products and put them to the Cart
     *
     * @param string $orderNumber
     * @param string $storeId
     * @return Data\ReorderOutput
     * @throws InputException Order is not found
     * @throws NoSuchEntityException The specified customer does not exist.
     * @throws \Magento\Framework\Exception\CouldNotSaveException Could not create customer Cart
     */
    public function execute(string $orderNumber, string $storeId): Data\ReorderOutput
    {
        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, $storeId);

        if (!$order->getId()) {
            throw new InputException(
                __('Cannot find order number "%1" in store "%2"', $orderNumber, $storeId)
            );
        }
        $customerId = (int)$order->getCustomerId();
        $this->errors = [];

        $cart = $this->customerCartProvider->resolve($customerId);
        if (!$this->reorderHelper->isAllowed($order->getStore())) {
            $this->addError((string)__('Reorders are not allowed.'), self::ERROR_REORDER_NOT_AVAILABLE);
            return $this->prepareOutput($cart);
        }

        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            $this->addOrderItem($cart, $item);
        }

        try {
            $this->cartRepository->save($cart);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // handle exception from \Magento\Quote\Model\QuoteRepository\SaveHandler::save
            $this->addError($e->getMessage());
        }

        return $this->prepareOutput($cart);
    }

    /**
     * Convert order item to quote item
     *
     * @param \Magento\Quote\Model\Quote $cart
     * @param Item $orderItem
     * @return void
     */
    private function addOrderItem(\Magento\Quote\Model\Quote $cart, $orderItem): void
    {
        /* @var $orderItem Item */
        if ($orderItem->getParentItem() === null) {
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            $info->setQty($orderItem->getQtyOrdered());

            try {
                $product = $this->productRepository->getById($orderItem->getProductId(), false, null, true);
            } catch (NoSuchEntityException $e) {
                $this->addError(
                    (string)__('Could not find a product with ID "%1"', $orderItem->getProductId()),
                    self::ERROR_PRODUCT_NOT_FOUND
                );
                return;
            }
            try {
                $cart->addProduct($product, $info);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->addError($this->addCartItemError($product->getSku(), $e->getMessage()));
            } catch (\Throwable $e) {
                $this->logger->critical($e);
                $this->addError($this->addCartItemError($product->getSku()), self::ERROR_UNDEFINED);
            }
        }
    }

    /**
     * Add order line item error
     *
     * @param string $message
     * @param string|null $code
     * @return void
     */
    private function addError(string $message, string $code = null): void
    {
        $this->errors[] = new Data\Error(
            $message,
            $code ?? $this->getErrorCode($message)
        );
    }

    /**
     * Get message error code. Ad-hoc solution based on message parsing.
     *
     * @param string $message
     * @return string
     */
    private function getErrorCode(string $message): string
    {
        $code = self::ERROR_UNDEFINED;

        $matchedCodes = array_filter(
            self::MESSAGE_CODES,
            function ($key) use ($message) {
                return false !== strpos($message, $key);
            },
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($matchedCodes)) {
            $code = current($matchedCodes);
        }

        return $code;
    }

    /**
     * Prepare output
     *
     * @param CartInterface $cart
     * @return Data\ReorderOutput
     */
    private function prepareOutput(CartInterface $cart): Data\ReorderOutput
    {
        $output = new Data\ReorderOutput($cart, $this->errors);
        $this->errors = [];
        return $output;
    }

    /**
     * Add error message for a cart item
     *
     * @param string $sku
     * @param string|null $message
     * @return string
     */
    private function addCartItemError(string $sku, string $message = null): string
    {
        return (string)($message
            ? __('Could not add the product with SKU "%1" to the shopping cart: %2', $sku, $message)
            : __('Could not add the product with SKU "%1" to the shopping cart', $sku));
    }
}
