<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart;

/**
 * Shopping cart validation messages block
 */
class ValidationMessages extends \Magento\Framework\View\Element\Messages
{
    /** @var \Magento\Checkout\Helper\Cart */
    protected $cartHelper;

    /** @var \Magento\Framework\Locale\CurrencyInterface */
    protected $currency;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $data
        );
        $this->cartHelper = $cartHelper;
        $this->currency = $currency;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->cartHelper->getItemsCount()) {
            $this->validateMinimumAmount();
            $this->addQuoteMessages();
            $this->addMessages($this->messageManager->getMessages(true));
        }
        return parent::_prepareLayout();
    }

    /**
     * Validate minimum amount and display notice in error
     *
     * @return void
     */
    protected function validateMinimumAmount()
    {
        if (!$this->cartHelper->getQuote()->validateMinimumAmount()) {
            $warning = $this->_scopeConfig->getValue(
                'sales/minimum_order/description',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if (!$warning) {
                $currencyCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                $minimumAmount = $this->currency->getCurrency($currencyCode)->toCurrency(
                    $this->_scopeConfig->getValue(
                        'sales/minimum_order/amount',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
                );
                $warning = __('Minimum order amount is %1', $minimumAmount);
            }
            $this->messageManager->addNotice($warning);
        }
    }

    /**
     * Add quote messages
     *
     * @return void
     */
    protected function addQuoteMessages()
    {
        // Compose array of messages to add
        $messages = [];
        /** @var \Magento\Framework\Message\MessageInterface $message */
        foreach ($this->cartHelper->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setText($this->escapeHtml($message->getText()));
                $messages[] = $message;
            }
        }
        $this->messageManager->addUniqueMessages($messages);
    }
}
