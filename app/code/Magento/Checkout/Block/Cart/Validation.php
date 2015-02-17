<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

/**
 * Shopping cart validation messages block
 */
class Validation extends \Magento\Framework\View\Element\AbstractBlock
{
    /** @var \Magento\Checkout\Helper\Cart */
    protected $cartHelper;

    /** @var \Magento\Customer\Model\Session */
    protected $customerSession;

    /** @var \Magento\Framework\Locale\CurrencyInterface */
    protected $currency;

    /** @var \Magento\Framework\Store\StoreManagerInterface */
    protected $storeManager;

    /** @var \Magento\Framework\Message\ManagerInterface */
    protected $messages;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Message\ManagerInterface $messages
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Store\StoreManagerInterface $storeManager,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Message\ManagerInterface $messages,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
        $this->cartHelper = $cartHelper;
        $this->customerSession = $customerSession;
        $this->currency = $currency;
        $this->storeManager = $storeManager;
        $this->messages = $messages;
        $this->_isScopePrivate = true;

        if ($customerSession->getCustomerId()) {
            $this->validateMinimunAmount();
            $this->addQuoteMessages();
        }
    }

    /**
     * Validate minimum amount and display notice in error
     */
    public function validateMinimunAmount()
    {
        if (!$this->cartHelper->getQuote()->validateMinimumAmount()) {
            $warning = $this->_scopeConfig->getValue(
                'sales/minimum_order/description',
                \Magento\Framework\Store\ScopeInterface::SCOPE_STORE
            );
            if (!$warning) {
                $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
                $minimumAmount = $this->currency->getCurrency($currencyCode)->toCurrency(
                    $this->_scopeConfig->getValue(
                        'sales/minimum_order/amount',
                        \Magento\Framework\Store\ScopeInterface::SCOPE_STORE
                    )
                );
                $warning = __('Minimum order amount is %1', $minimumAmount);
            }
            $this->messages->addNotice($warning);
        }
    }

    /**
     * Add quote messages
     */
    public function addQuoteMessages()
    {
        // Compose array of messages to add
        $messages = [];
        /** @var \Magento\Framework\Message\MessageInterface $message  */
        foreach ($this->cartHelper->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setText($this->escapeHtml($message->getText()));
                $messages[] = $message;
            }
        }
        $this->messages->addUniqueMessages($messages);
    }
}
