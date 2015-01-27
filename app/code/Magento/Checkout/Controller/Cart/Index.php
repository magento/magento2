<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Checkout\Controller\Cart;

class Index extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param CustomerCart $cart
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $resultRedirectFactory
        );
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Shopping cart display action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_eventManager->dispatch('collect_totals_failed_items');
        if ($this->cart->getQuote()->getItemsCount()) {
            $this->cart->init();
            $this->cart->save();

            if (!$this->cart->getQuote()->validateMinimumAmount()) {
                $currencyCode = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
                    ->getStore()
                    ->getCurrentCurrencyCode();
                $minimumAmount = $this->_objectManager->get('Magento\Framework\Locale\CurrencyInterface')
                    ->getCurrency($currencyCode)
                    ->toCurrency(
                        $this->_scopeConfig->getValue(
                            'sales/minimum_order/amount',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                );

                $warning = $this->_scopeConfig->getValue(
                    'sales/minimum_order/description',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) ? $this->_scopeConfig->getValue(
                    'sales/minimum_order/description',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                ) : __(
                    'Minimum order amount is %1',
                    $minimumAmount
                );

                $this->messageManager->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = [];
        /** @var \Magento\Framework\Message\MessageInterface $message  */
        foreach ($this->cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setText($this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message->getText()));
                $messages[] = $message;
            }
        }
        $this->messageManager->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_checkoutSession->setCartWasUpdated(true);

        \Magento\Framework\Profiler::start(__METHOD__ . 'cart_display');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->initMessages();
        $resultPage->getConfig()->getTitle()->set(__('Shopping Cart'));
        \Magento\Framework\Profiler::stop(__METHOD__ . 'cart_display');
        return $resultPage;
    }
}
