<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\GiftMessage\Helper\Message;

/**
 * Adminhtml sales order create gift message form
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Form extends Template
{
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $_sessionQuote;

    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Message $messageHelper,
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
     */
    public function canDisplayGiftmessageForm()
    {
        $quote = $this->_sessionQuote->getQuote();
        return $this->_messageHelper->isMessagesAllowed('items', $quote, $quote->getStore());
    }
}
