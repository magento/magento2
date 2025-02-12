<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Sales\Model\Reorder;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Cart\CustomerCartResolver;
use Magento\Quote\Model\GuestCart\GuestCartResolver;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Helper\Reorder as ReorderHelper;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection as ItemCollection;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

/**
 * Allows customer quickly to reorder previously added products and put them to the Cart
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Reorder
{
    /**
     * Forbidden reorder item properties
     */
    private const FORBIDDEN_REORDER_PROPERTIES = ['custom_price'];

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
        'The required options you selected are not available' => self::ERROR_NOT_SALABLE,
        'Product that you are trying to add is not available' => self::ERROR_NOT_SALABLE,
        'This product is out of stock' => self::ERROR_NOT_SALABLE,
        'There are no source items' => self::ERROR_NOT_SALABLE,
        'The fewest you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The most you may purchase is' => self::ERROR_INSUFFICIENT_STOCK,
        'The requested qty is not available' => self::ERROR_INSUFFICIENT_STOCK,
        'Not enough items for sale' => self::ERROR_INSUFFICIENT_STOCK,
        'Only %s of %s available' => self::ERROR_INSUFFICIENT_STOCK,
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var Data\Error[]
     */
    private $errors = [];

    /**
     * @var CustomerCartResolver
     */
    private $customerCartProvider;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var GuestCartResolver
     */
    private $guestCartResolver;

    /**
     * @var OrderInfoBuyRequestGetter
     */
    private $orderInfoBuyRequestGetter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var bool
     */
    private bool $addToCartInvalidProduct;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param OrderFactory $orderFactory
     * @param CustomerCartResolver $customerCartProvider
     * @param GuestCartResolver $guestCartResolver
     * @param CartRepositoryInterface $cartRepository
     * @param ReorderHelper $reorderHelper
     * @param LoggerInterface $logger
     * @param ProductCollectionFactory $productCollectionFactory
     * @param OrderInfoBuyRequestGetter $orderInfoBuyRequestGetter
     * @param StoreManagerInterface|null $storeManager
     * @param bool $addToCartInvalidProduct
     * @param CustomerSession|null $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        OrderFactory $orderFactory,
        CustomerCartResolver $customerCartProvider,
        GuestCartResolver $guestCartResolver,
        CartRepositoryInterface $cartRepository,
        ReorderHelper $reorderHelper,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        OrderInfoBuyRequestGetter $orderInfoBuyRequestGetter,
        ?StoreManagerInterface   $storeManager = null,
        bool $addToCartInvalidProduct = false,
        ?CustomerSession $customerSession = null
    ) {
        $this->orderFactory = $orderFactory;
        $this->cartRepository = $cartRepository;
        $this->reorderHelper = $reorderHelper;
        $this->logger = $logger;
        $this->customerCartProvider = $customerCartProvider;
        $this->guestCartResolver = $guestCartResolver;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->orderInfoBuyRequestGetter = $orderInfoBuyRequestGetter;
        $this->storeManager = $storeManager
            ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->addToCartInvalidProduct = $addToCartInvalidProduct;
        $this->customerSession = $customerSession
            ?: ObjectManager::getInstance()->get(CustomerSession::class);
    }

    /**
     * Allows customer quickly to reorder previously added products and put them to the Cart
     *
     * @param string $orderNumber
     * @param string $storeId
     * @return Data\ReorderOutput
     * @throws InputException Order is not found
     * @throws NoSuchEntityException The specified customer does not exist.
     * @throws CouldNotSaveException
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function execute(string $orderNumber, string $storeId): Data\ReorderOutput
    {
        $order = $this->orderFactory->create()->loadByIncrementIdAndStoreId($orderNumber, $storeId);

        if (!$order->getId()) {
            throw new InputException(
                __('Cannot find order number "%1" in store "%2"', $orderNumber, $storeId)
            );
        }
        $customerId = (int) $order->getCustomerId();
        $this->errors = [];

        $cart = $this->isCustomerReorderAsGuest($customerId)
            ? $this->guestCartResolver->resolve()
            : $this->customerCartProvider->resolve($customerId);
        if (!$this->reorderHelper->isAllowed($order->getStore())) {
            $this->addError((string)__('Reorders are not allowed.'), self::ERROR_REORDER_NOT_AVAILABLE);
            return $this->prepareOutput($cart);
        }

        $storeId = (string) $this->storeManager->getStore()->getId();
        $this->addItemsToCart($cart, $order->getItemsCollection(), $storeId);

        try {
            $this->cartRepository->save($cart);
        } catch (LocalizedException $e) {
            // handle exception from \Magento\Quote\Model\QuoteRepository\SaveHandler::save
            $this->addError($e->getMessage());
        }

        $savedCart = $this->cartRepository->get($cart->getId());

        return $this->prepareOutput($savedCart);
    }

    /**
     * Add collections of order items to cart.
     *
     * @param Quote $cart
     * @param ItemCollection $orderItems
     * @param string $storeId
     * @return void
     * @throws LocalizedException
     */
    private function addItemsToCart(Quote $cart, ItemCollection $orderItems, string $storeId): void
    {
        $orderItemProductIds = [];
        /** @var Item[] $orderItemsByProductId */
        $orderItemsByProductId = [];

        /** @var Item $item */
        foreach ($orderItems as $item) {
            if ($item->getParentItem() === null) {
                $orderItemProductIds[] = $item->getProductId();
                $orderItemsByProductId[$item->getProductId()][$item->getId()] = $item;
            }
        }

        $products = $this->getOrderProducts($storeId, $orderItemProductIds);

        // compare founded products and throw an error if some product not exists
        $productsNotFound = array_diff($orderItemProductIds, array_keys($products));
        if (!empty($productsNotFound)) {
            foreach ($productsNotFound as $productId) {
                /** @var Item $orderItemProductNotFound */
                $this->addError(
                    (string)__('Could not find a product with ID "%1"', $productId),
                    self::ERROR_PRODUCT_NOT_FOUND
                );
            }
        }

        foreach ($orderItemsByProductId as $productId => $orderItems) {
            if (!isset($products[$productId])) {
                continue;
            }
            $product = $products[$productId];
            foreach ($orderItems as $orderItem) {
                $this->addItemToCart($orderItem, $cart, clone $product);
            }
        }
    }

    /**
     * Get order products by store id and order item product ids.
     *
     * @param string $storeId
     * @param int[] $orderItemProductIds
     * @return Product[]
     * @throws LocalizedException
     */
    private function getOrderProducts(string $storeId, array $orderItemProductIds): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setFlag('has_stock_status_filter', true);
        $collection->setStore($storeId)
            ->addIdFilter($orderItemProductIds)
            ->addStoreFilter()
            ->addAttributeToSelect('*')
            ->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner')
            ->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner')
            ->addOptionsToResult();

        return $collection->getItems();
    }

    /**
     * Adds order item product to cart.
     *
     * @param OrderItemInterface $orderItem
     * @param Quote $cart
     * @param ProductInterface $product
     * @return void
     */
    private function addItemToCart(OrderItemInterface $orderItem, Quote $cart, ProductInterface $product): void
    {
        $infoBuyRequest = $this->orderInfoBuyRequestGetter->getInfoBuyRequest($orderItem);
        $this->sanitizeBuyRequest($infoBuyRequest);

        $addProductResult = null;
        try {
            $infoBuyRequest->setAddToCartInvalidProduct($this->addToCartInvalidProduct);
            $addProductResult = $cart->addProduct($product, $infoBuyRequest);
        } catch (LocalizedException $e) {
            $this->addError($this->getCartItemErrorMessage($orderItem, $product, $e->getMessage()));
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            $this->addError($this->getCartItemErrorMessage($orderItem, $product), self::ERROR_UNDEFINED);
        }

        // error happens in case the result is string
        if (is_string($addProductResult)) {
            $errors = array_unique(explode("\n", $addProductResult));
            foreach ($errors as $error) {
                $this->addError($this->getCartItemErrorMessage($orderItem, $product, $error));
            }
        }
    }

    /**
     * Removes forbidden reorder item properties
     *
     * @param DataObject $dataObject
     * @return void
     */
    private function sanitizeBuyRequest(DataObject $dataObject): void
    {
        foreach (self::FORBIDDEN_REORDER_PROPERTIES as $forbiddenProp) {
            if ($dataObject->hasData($forbiddenProp)) {
                $dataObject->unsetData($forbiddenProp);
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
    private function addError(string $message, ?string $code = null): void
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
        $message = preg_replace('/\d+/', '%s', $message);
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
        // we already show user errors, do not expose it to cart level
        $cart->setHasError(false);
        return $output;
    }

    /**
     * Get error message for a cart item
     *
     * @param Item $item
     * @param Product $product
     * @param string|null $message
     * @return string
     */
    private function getCartItemErrorMessage(Item $item, Product $product, ?string $message = null): string
    {
        // try to get sku from line-item first.
        // for complex product type: if custom option is not available it can cause error
        $sku = $item->getSku() ?? $product->getData('sku');
        return (string)($message
            ? __('Could not add the product with SKU "%1" to the shopping cart: %2', $sku, $message)
            : __('Could not add the product with SKU "%1" to the shopping cart', $sku));
    }

    /**
     * Check customer re-order as guest customer
     *
     * @param int $customerId
     * @return bool
     */
    private function isCustomerReorderAsGuest(int $customerId): bool
    {
        return $customerId === 0 || !$this->customerSession->isLoggedIn();
    }
}
