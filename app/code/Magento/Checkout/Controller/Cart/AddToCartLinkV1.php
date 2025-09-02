<?php
declare(strict_types=1);

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

namespace Magento\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Controller for Meta Checkout URL implementation
 * Handles dynamic store scope switching via the 'store' URL parameter.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCartLinkV1 implements HttpGetActionInterface
{
    /**
     * Configuration path for enabling the Add To Cart Link feature.
     */
    const XML_PATH_ENABLE_ADD_TO_CART_LINK = 'checkout/cart/enable_add_to_cart_link_v1';

    /**
     * Maximum number of products that can be processed in a single request to
     * prevent abuse (DoS via extremely large cart payloads).
     */
    private const MAX_PRODUCTS_PER_REQUEST = 25;

    /**
     * Request instance
     *
     * @var RequestInterface
     */
    private $_request;

    /**
     * Constructor
     *
     * @param Context                    $context           Context
     * @param CheckoutSession            $checkoutSession   Checkout session
     * @param ProductRepositoryInterface $productRepository Product repository
     * @param PageFactory                $resultPageFactory Result page factory
     * @param ManagerInterface           $messageManager    Message manager
     * @param ScopeConfigInterface       $scopeConfig       Scope configuration
     * @param ForwardFactory             $resultForwardFactory Result forward factory
     * @param StoreManagerInterface      $storeManager      Store manager
     * @param CartRepositoryInterface    $cartRepository    Cart repository
     */
    public function __construct(
        Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly PageFactory $resultPageFactory,
        private readonly ManagerInterface $messageManager,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly ForwardFactory $resultForwardFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly CartRepositoryInterface $cartRepository
    ) {
        $this->_request = $context->getRequest();
    }

    /**
     * Execute action based on request and return result
     * Handles store switching and populates cart based on URL parameters.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        // Handle store switching first
        $storeParam = $this->_request->getParam('store');
        $store = null;
        try {
            if ($storeParam) {
                $store = $this->storeManager->getStore($storeParam);
                if (!$store->getIsActive()) {
                    $store = null;
                }
            }
        } catch (\Exception $e) {
            $store = null;
        }
        if (!$store) {
            $store = $this->storeManager->getDefaultStoreView();
        }
        $this->storeManager->setCurrentStore($store->getId());

        // Check if the feature is enabled for the current (potentially switched) store scope
        if (!$this->scopeConfig->isSetFlag(self::XML_PATH_ENABLE_ADD_TO_CART_LINK, ScopeInterface::SCOPE_STORE)) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }

        // Get products parameter
        $productsParam = $this->_request->getParam('products', '');
        $couponCode = $this->_request->getParam('coupon', '');

        // Get quote from checkout session (should now reflect the correct store)
        try {
            $quote = $this->checkoutSession->getQuote();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Unable to initialize the shopping cart.'));
            return $this->resultPageFactory->create();
        }

        // Ensure quote is associated with the correct store ID after potential switch
        try {
            $currentStoreId = $this->storeManager->getStore()->getId();
            if ($quote->getStoreId() !== $currentStoreId) {
                $quote->setStoreId($currentStoreId);
                // May need to reload or recalculate parts of the quote if store change affects it
            }
        } catch (NoSuchEntityException $e) {
            // Store could not be resolved – fall back to default behaviour and continue
        }

        // Clear the quote first (required by Meta spec)
        $quote->removeAllItems();

        // Parse products parameter
        if (!empty($productsParam)) {
            $productItems = $this->_parseProductsParam($productsParam);

            // Enforce maximum allowed items in one request
            if (count($productItems) > self::MAX_PRODUCTS_PER_REQUEST) {
                $this->messageManager->addErrorMessage(
                    __('You can only add up to %1 products at once.', self::MAX_PRODUCTS_PER_REQUEST)
                );
                $productItems = array_slice($productItems, 0, self::MAX_PRODUCTS_PER_REQUEST);
            }

            // Add products to quote
            foreach ($productItems as $item) {
                try {
                    $productIdentifier = $item['identifier'];
                    $qty = $item['qty'];
                    $product = null;

                    // Load product within the current store scope
                    $currentStoreId = $this->storeManager->getStore()->getId();

                    // First try to load by SKU for the current store
                    try {
                        // Pass store ID to ensure product is loaded in the correct context if needed
                        // Note: ProductRepository->get() might not directly accept storeId.
                        // Custom logic or preference for store-specific SKUs might be required here.
                        $product = $this->productRepository->get($productIdentifier);
                        // Verify product is available in the current store
                        if (!in_array($currentStoreId, $product->getStoreIds())) {
                             throw new NoSuchEntityException(__('Product is not available in the selected store.'));
                        }

                    } catch (NoSuchEntityException $e) {
                        // If SKU lookup fails, try by ID (less likely to be store specific identifier)
                        try {
                            $product = $this->productRepository->getById((int)$productIdentifier, false, $currentStoreId);
                            // Verify product is available in the current store
                            if (!in_array($currentStoreId, $product->getStoreIds())) {
                                 throw new NoSuchEntityException(__('Product is not available in the selected store.'));
                            }
                        } catch (NoSuchEntityException $idException) {
                            // Both SKU and ID lookup failed for the current store
                            $this->messageManager->addErrorMessage(
                                __(
                                    'Product with identifier "%1" was not found in the current store.',
                                    $productIdentifier
                                )
                            );
                            continue;
                        } catch (\InvalidArgumentException $invalidIdException) {
                             // ID was not an integer
                            $this->messageManager->addErrorMessage(
                                __(
                                    'Product identifier "%1" is invalid.',
                                    $productIdentifier
                                )
                            );
                            continue;
                        }
                    }

                     // Ensure product is salable in the current store context
                    if (!$product->isSalable()) {
                        $this->messageManager->addErrorMessage(
                            __('Product "%1" is currently out of stock or not available for purchase.', $product->getName())
                        );
                        continue;
                    }


                    // Add product to quote using the product object
                    // Ensure the request object correctly represents quantity
                    $requestInfo = new \Magento\Framework\DataObject(['qty' => $qty]);
                    $quote->addProduct($product, $requestInfo);

                } catch (NoSuchEntityException $e) {
                    // Catch store specific availability issues
                     $this->messageManager->addErrorMessage($e->getMessage());
                     continue;
                } catch (\Exception $e) {
                    // Other exceptions, log and continue
                    $this->messageManager->addErrorMessage(__('Could not add product to cart: %1', $e->getMessage()));
                    // Consider logging $e for debugging
                    continue;
                }
            }

            // Save quote and collect totals
            $quote->collectTotals();
            try {
                $this->cartRepository->save($quote);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Unable to save cart.'));
            }

            // Update checkout session
            $this->checkoutSession->setQuoteId($quote->getId());
        }

        // Apply coupon code if provided
        if (!empty($couponCode)) {
            try {
                $quote->setCouponCode($couponCode)->collectTotals();
                $this->cartRepository->save($quote);

                // Check if coupon was actually applied
                if ($quote->getCouponCode() !== $couponCode) {
                    // Coupon might be invalid or not applicable to the current store/cart contents
                    $this->messageManager->addErrorMessage(
                        __('The coupon code "%1" is not valid or cannot be applied.', $couponCode)
                    );
                }
            } catch (\Exception $e) {
                // Log the error for debugging
                $this->messageManager->addErrorMessage(
                    __('Could not apply coupon code "%1".', $couponCode)
                );
            }
        }

        // Render the checkout page directly (not a redirect)
        // This ensures the URL parameters remain in the browser address bar
        return $this->resultPageFactory->create();
    }

    /**
     * Parse the products parameter from the URL
     *
     * Format: identifier:qty,identifier:qty
     * (where identifier can be SKU or product ID)
     *
     * @param string $productsParam Products parameter string
     *
     * @return array<int, array<string, mixed>>
     */
    private function _parseProductsParam(string $productsParam): array
    {
        $result = [];
        $productPairs = explode(',', $productsParam);

        foreach ($productPairs as $pair) {
            $pair = trim($pair); // Trim whitespace from each pair
            if (empty($pair)) {
                continue; // Skip empty entries potentially caused by trailing commas
            }
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $identifier = trim($parts[0]);
                $qty = filter_var($parts[1], FILTER_VALIDATE_INT); // Validate quantity is an integer

                // Ensure identifier is not empty and quantity is a positive integer
                if (!empty($identifier) && $qty !== false && $qty > 0) {
                    $result[] = [
                        'identifier' => $identifier,
                        'qty' => $qty
                    ];
                } else {
                    // Log or message invalid format part
                     $this->messageManager->addWarningMessage(__('Invalid format or quantity for product entry "%1".', $pair));
                }
            } else {
                 // Log or message invalid format pair
                  $this->messageManager->addWarningMessage(__('Invalid format for product entry "%1". Expected format: identifier:qty.', $pair));
            }
        }

        return $result;
    }
}
