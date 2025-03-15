<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Checkout\Model\Cart;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;

/**
 * Controller for Meta Checkout URL implementation
 */
class AddToCartLinkV1 implements HttpGetActionInterface
{

    /**
     * Request instance
     *
     * @var RequestInterface
     */
    private $_request;

    /**
     * Constructor
     *
     * @param Context                    $context               Context
     * @param CheckoutSession            $checkoutSession       Checkout session
     * @param ProductRepositoryInterface $productRepository     Product repository
     * @param Cart                       $cart                  Cart
     * @param PageFactory                $resultPageFactory     Result page factory
     * @param RedirectFactory            $resultRedirectFactory Redirect factory
     * @param CouponFactory              $couponFactory         Coupon factory
     * @param Usage                      $couponUsage           Coupon usage
     * @param ManagerInterface           $messageManager        Message manager
     */
    public function __construct(
        Context $context,
        private readonly CheckoutSession $checkoutSession,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly Cart $cart,
        private readonly PageFactory $resultPageFactory,
        private readonly RedirectFactory $resultRedirectFactory,
        private readonly CouponFactory $couponFactory,
        private readonly Usage $couponUsage,
        private readonly ManagerInterface $messageManager
    ) {
        $this->_request = $context->getRequest();
    }

    /**
     * Execute action based on request and return result
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        // Get products parameter
        $productsParam = $this->_request->getParam('products', '');
        $couponCode = $this->_request->getParam('coupon', '');

        // Clear the cart first (required by Meta spec)
        $this->cart->truncate();

        // Parse products parameter
        if (!empty($productsParam)) {
            $productItems = $this->_parseProductsParam($productsParam);

            // Add products to cart
            foreach ($productItems as $item) {
                try {
                    $productIdentifier = $item['identifier'];
                    $qty = $item['qty'];
                    $product = null;

                    // First try to load by SKU
                    try {
                        $product = $this->productRepository->get($productIdentifier);
                    } catch (NoSuchEntityException $e) {
                        // If SKU lookup fails, try by ID
                        try {
                            $product = $this->productRepository->getById($productIdentifier);
                        } catch (NoSuchEntityException $idException) {
                            // Both SKU and ID lookup failed
                            $this->messageManager->addErrorMessage(
                                __(
                                    'Product with identifier "%1" was not found.', 
                                    $productIdentifier
                                )
                            );
                            continue;
                        }
                    }

                    // Add product to cart using the product object
                    $this->cart->addProduct($product, ['qty' => $qty]);
                } catch (\Exception $e) {
                    // Other exceptions, continue with next item
                    $this->messageManager->addErrorMessage($e->getMessage());
                    continue;
                }
            }

            // Save cart
            $this->cart->save();
        }

        // Apply coupon code if provided
        if (!empty($couponCode)) {
            try {
                $this->cart->getQuote()->setCouponCode($couponCode);
                $this->cart->save();

                // Check if coupon was actually applied
                if ($this->cart->getQuote()->getCouponCode() !== $couponCode) {
                    $this->messageManager->addErrorMessage(
                        __('The coupon code "%1" is not valid.', $couponCode)
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The coupon code "%1" is not valid: %2', $couponCode, $e->getMessage())
                );
            }
        }

        // Render the checkout page directly (not a redirect)
        // This ensures the URL parameters remain in the browser address bar
        return $this->resultPageFactory->create();
    }

    /**
     * Parse the products parameter from the URL
     * Format: identifier:qty,identifier:qty (where identifier can be SKU or product ID)
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
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $result[] = [
                    'identifier' => $parts[0],
                    'qty' => (int)$parts[1]
                ];
            }
        }

        return $result;
    }
}
