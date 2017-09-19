<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml order create gift message block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Giftmessage extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Gift message save
     *
     * @var \Magento\GiftMessage\Model\Save
     */
    protected $_giftMessageSave;

    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\GiftMessage\Model\Save $giftMessageSave
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\GiftMessage\Model\Save $giftMessageSave,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        array $data = []
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_giftMessageSave = $giftMessageSave;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $data);
    }

    /**
     * Generate form for editing of gift message for entity
     *
     * @param \Magento\Framework\DataObject $entity
     * @param string $entityType
     * @return string
     */
    public function getFormHtml(\Magento\Framework\DataObject $entity, $entityType = 'quote')
    {
        return $this->getLayout()->createBlock(
            \Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form::class
        )->setEntity(
            $entity
        )->setEntityType(
            $entityType
        )->toHtml();
    }

    /**
     * Retrieve items allowed for gift messages.
     *
     * If no items available return false.
     *
     * @return array|false
     */
    public function getItems()
    {
        $items = [];
        $allItems = $this->getQuote()->getAllItems();

        foreach ($allItems as $item) {
            if ($this->_getGiftmessageSaveModel()->getIsAllowedQuoteItem(
                $item
            ) && $this->_messageHelper->isMessagesAllowed(
                'item',
                $item,
                $this->getStore()
            )
            ) {
                // if item allowed
                $items[] = $item;
            }
        }

        if (sizeof($items)) {
            return $items;
        }

        return false;
    }

    /**
     * Retrieve gift message save model
     *
     * @return \Magento\GiftMessage\Model\Save
     */
    protected function _getGiftmessageSaveModel()
    {
        return $this->_giftMessageSave;
    }
}
