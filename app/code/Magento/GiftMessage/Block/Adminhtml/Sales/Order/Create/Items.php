<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\GiftMessage\Helper\Message;
use Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form as GiftMessageForm;

/**
 * Gift message adminhtml sales order create items
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Items extends Template
{
    /**
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Message $messageHelper,
        array $data = []
    ) {
        $this->_messageHelper = $messageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get order item
     *
     * @return \Magento\Quote\Model\Quote\Item
     * @codeCoverageIgnore
     */
    public function getItem()
    {
        return $this->getParentBlock()->getItem();
    }

    /**
     * Indicates that block can display gift messages form
     *
     * @return boolean
     */
    public function canDisplayGiftMessage()
    {
        $item = $this->getItem();
        if (!$item) {
            return false;
        }
        return $this->_messageHelper->isMessagesAllowed('item', $item, $item->getStoreId());
    }

    /**
     * Return form html
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFormHtml()
    {
        return $this->getLayout()->createBlock(
            GiftMessageForm::class
        )->setEntity(
            $this->getItem()
        )->setEntityType(
            'item'
        )->toHtml();
    }

    /**
     * Retrieve gift message for item
     *
     * @return string
     */
    public function getMessageText()
    {
        if ($this->getItem()->getGiftMessageId()) {
            $model = $this->_messageHelper->getGiftMessage($this->getItem()->getGiftMessageId());
            return $this->escapeHtml($model->getMessage());
        }
        return '';
    }
}
