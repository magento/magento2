<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

/**
 * Gift message adminhtml sales order create items
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Items extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\GiftMessage\Helper\Message
     * @since 2.0.0
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\GiftMessage\Helper\Message $messageHelper,
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
     * @since 2.0.0
     */
    public function getItem()
    {
        return $this->getParentBlock()->getItem();
    }

    /**
     * Indicates that block can display gift messages form
     *
     * @return boolean
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getFormHtml()
    {
        return $this->getLayout()->createBlock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form::class
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
     * @since 2.0.0
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
