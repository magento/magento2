<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\GiftMessage\Block\Adminhtml\Sales\Order\Create;

/**
 * Gift message adminhtml sales order create items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Items extends \Magento\Backend\Block\Template
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
        \Magento\Backend\Block\Template\Context $context,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        array $data = array()
    ) {
        $this->_messageHelper = $messageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get order item
     *
     * @return \Magento\Sales\Model\Quote\Item
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
        return $this->_messageHelper->getIsMessagesAvailable('item', $item, $item->getStoreId());
    }

    /**
     * Return form html
     *
     * @return string
     */
    public function getFormHtml()
    {
        return $this->getLayout()->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\Create\Giftmessage\Form'
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
