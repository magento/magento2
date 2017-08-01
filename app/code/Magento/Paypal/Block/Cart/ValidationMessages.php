<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Cart;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * PayPal order review page validation messages block
 *
 * @api
 * @since 2.0.0
 */
class ValidationMessages extends \Magento\Framework\View\Element\Messages
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     * @since 2.0.0
     */
    protected $cartHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Checkout\Helper\Cart $cartHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $data
        );
        $this->cartHelper = $cartHelper;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        if ($this->cartHelper->getItemsCount()) {
            $this->addQuoteMessages();
            $this->addMessages($this->messageManager->getMessages(true));
        }
        return parent::_prepareLayout();
    }

    /**
     * Add quote messages
     *
     * @return void
     * @since 2.0.0
     */
    protected function addQuoteMessages()
    {
        // Compose array of messages to add
        $messages = [];
        /** @var MessageInterface $message */
        foreach ($this->cartHelper->getQuote()->getMessages() as $message) {
            if (!$message->getIdentifier()) {
                try {
                    $messages[] = $this->messageManager
                        ->createMessage($message->getType())
                        ->setText($message->getText());
                } catch (\InvalidArgumentException $e) {
                    // pass
                }
            } else {
                $messages[] = $message;
            }
        }
        $this->messageManager->addUniqueMessages(
            $messages
        );
    }
}
