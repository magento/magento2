<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

class CouponPost extends \Magento\Checkout\Controller\Cart
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
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
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->cart->getQuote()->getItemsCount()) {
            return $this->_goBack();
        }

        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));
        $oldCouponCode = $this->cart->getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        $codeLength = strlen($couponCode);
        $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

        $this->cart->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        $this->cart->getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
        $this->quoteRepository->save($this->cart->getQuote());

        if ($codeLength) {
            if ($isCodeLengthValid && $couponCode == $this->cart->getQuote()->getCouponCode()) {
                $this->messageManager->addSuccess(
                    __(
                        'You used coupon code "%1".',
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
                $this->cart->save();
            }
        } else {
            $this->messageManager->addSuccess(__('You canceled the coupon code.'));
        }

        return $this->_goBack();
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getDefaultResult()
    {
        return $this->_goBack();
    }
}
