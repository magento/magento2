<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

/**
 * Adminhtml sales order create gift message form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Form extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     * @since 2.0.0
     */
    protected $_sessionQuote;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     * @since 2.0.0
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        array $data = []
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_sessionQuote = $sessionQuote;
        parent::__construct($context, $data);
    }

    /**
     * Indicates that block can display gift message form
     *
     * @return bool
     * @since 2.0.0
     */
    public function canDisplayGiftmessageForm()
    {
        $quote = $this->_sessionQuote->getQuote();
        return $this->_messageHelper->isMessagesAllowed('items', $quote, $quote->getStore());
    }
}
