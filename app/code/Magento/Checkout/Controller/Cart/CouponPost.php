<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;

class CouponPost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param CustomerCart $cart
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Sales\Model\QuoteRepository $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return void
     */
    public function execute()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->cart->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = $this->getRequest()->getParam(
            'remove'
        ) == 1 ? '' : trim(
            $this->getRequest()->getParam('coupon_code')
        );
        $oldCouponCode = $this->cart->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

            $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->cart->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
            $this->quoteRepository->save($this->cart->getQuote());

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->cart->getQuote()->getCouponCode()) {
                    $this->messageManager->addSuccess(
                        __(
                            'The coupon code "%1" was applied.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($couponCode)
                        )
                    );
                } else {
                    $this->messageManager->addError(
                        __(
                            'The coupon code "%1" is not valid.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($couponCode)
                        )
                    );
                }
            } else {
                $this->messageManager->addSuccess(__('The coupon code was canceled.'));
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We cannot apply the coupon code.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_goBack();
    }
}
