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
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Action\Context;

/**
 * Controller for Meta Checkout URL implementation
 */
class AddToCartLinkV1 implements HttpGetActionInterface, ActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;
    
    /**
     * @var CouponFactory
     */
    private $couponFactory;
    
    /**
     * @var Usage
     */
    private $couponUsage;
    
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param ProductRepositoryInterface $productRepository
     * @param Cart $cart
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param CouponFactory $couponFactory
     * @param Usage $couponUsage
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        ProductRepositoryInterface $productRepository,
        Cart $cart,
        PageFactory $resultPageFactory,
        RedirectFactory $resultRedirectFactory,
        CouponFactory $couponFactory,
        Usage $couponUsage,
        ManagerInterface $messageManager
    ) {
        $this->request = $context->getRequest();
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->couponFactory = $couponFactory;
        $this->couponUsage = $couponUsage;
        $this->messageManager = $messageManager;
    }

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // Get products parameter
        $productsParam = $this->request->getParam('products', '');
        $couponCode = $this->request->getParam('coupon', '');
        
        // Clear the cart first (required by Meta spec)
        $this->cart->truncate();
        
        // Parse products parameter
        if (!empty($productsParam)) {
            $productItems = $this->parseProductsParam($productsParam);
            
            // Add products to cart
            foreach ($productItems as $item) {
                try {
                    $productId = $item['product_id'];
                    $qty = $item['qty'];
                    
                    $product = $this->productRepository->getById($productId);
                    $this->cart->addProduct($product, ['qty' => $qty]);
                } catch (NoSuchEntityException $e) {
                    // Product not found, continue with next item
                    $this->messageManager->addErrorMessage(
                        __('Product with ID "%1" was not found.', $productId)
                    );
                    continue;
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
     * Format: product_id:qty,product_id:qty
     *
     * @param string $productsParam
     * @return array
     */
    private function parseProductsParam($productsParam)
    {
        $result = [];
        $productPairs = explode(',', $productsParam);
        
        foreach ($productPairs as $pair) {
            $parts = explode(':', $pair);
            if (count($parts) === 2) {
                $result[] = [
                    'product_id' => $parts[0],
                    'qty' => (int)$parts[1]
                ];
            }
        }
        
        return $result;
    }
}