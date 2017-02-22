<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View;

/**
 * Edit order giftmessage block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Giftmessage extends \Magento\Backend\Block\Widget
{
    /**
     * Entity for editing of gift message
     *
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected $_entity;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Message factory
     *
     * @var \Magento\GiftMessage\Model\MessageFactory
     */
    protected $_messageFactory;

    /**
     * Message helper
     *
     * @var \Magento\GiftMessage\Helper\Message
     */
    protected $_messageHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\GiftMessage\Model\MessageFactory $messageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\GiftMessage\Helper\Message $messageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\GiftMessage\Model\MessageFactory $messageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\GiftMessage\Helper\Message $messageHelper,
        array $data = []
    ) {
        $this->_messageHelper = $messageHelper;
        $this->_coreRegistry = $registry;
        $this->_messageFactory = $messageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Giftmessage object
     *
     * @var \Magento\GiftMessage\Model\Message
     */
    protected $_giftMessage;

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return void
     */
    protected function _beforeToHtml()
    {
        if ($this->getParentBlock() && ($order = $this->getOrder())) {
            $this->setEntity($order);
        }
        parent::_beforeToHtml();
    }

    /**
     * Prepares layout of block
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            ['label' => __('Save Gift Message'), 'class' => 'save']
        );

        return $this;
    }

    /**
     * Retrieve save button html
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        $this->getChildBlock(
            'save_button'
        )->setOnclick(
            'giftMessagesController.saveGiftMessage(\'' . $this->getHtmlId() . '\')'
        );

        return $this->getChildHtml('save_button');
    }

    /**
     * Set entity for form
     *
     * @param \Magento\Framework\DataObject $entity
     * @return $this
     */
    public function setEntity(\Magento\Framework\DataObject $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Retrieve entity for form
     *
     * @return \Magento\Framework\DataObject
     */
    public function getEntity()
    {
        if ($this->_entity === null) {
            $this->setEntity($this->_messageFactory->create()->getEntityModelByType('order'));
            $this->getEntity()->load($this->getRequest()->getParam('entity'));
        }
        return $this->_entity;
    }

    /**
     * Retrieve default value for giftmessage sender
     *
     * @return string
     */
    public function getDefaultSender()
    {
        if (!$this->getEntity()) {
            return '';
        }

        if ($this->getEntity()->getOrder()) {
            return $this->getEntity()->getOrder()->getCustomerName();
        }

        return $this->getEntity()->getCustomerName();
    }

    /**
     * Retrieve default value for giftmessage recipient
     *
     * @return string
     */
    public function getDefaultRecipient()
    {
        if (!$this->getEntity()) {
            return '';
        }

        if ($this->getEntity()->getOrder()) {
            if ($this->getEntity()->getOrder()->getShippingAddress()) {
                return $this->getEntity()->getOrder()->getShippingAddress()->getName();
            } elseif ($this->getEntity()->getOrder()->getBillingAddress()) {
                return $this->getEntity()->getOrder()->getBillingAddress()->getName();
            }
        }

        if ($this->getEntity()->getShippingAddress()) {
            return $this->getEntity()->getShippingAddress()->getName();
        } elseif ($this->getEntity()->getBillingAddress()) {
            return $this->getEntity()->getBillingAddress()->getName();
        }

        return '';
    }

    /**
     * Retrieve real name for field
     *
     * @param string $name
     * @return string
     */
    public function getFieldName($name)
    {
        return 'giftmessage[' . $this->getEntity()->getId() . '][' . $name . ']';
    }

    /**
     * Retrieve real html id for field
     *
     * @param string $id
     * @return string
     */
    public function getFieldId($id)
    {
        return $this->getFieldIdPrefix() . $id;
    }

    /**
     * Retrieve field html id prefix
     *
     * @return string
     */
    public function getFieldIdPrefix()
    {
        return 'giftmessage_order_' . $this->getEntity()->getId() . '_';
    }

    /**
     * Initialize gift message for entity
     *
     * @return $this
     */
    protected function _initMessage()
    {
        $this->_giftMessage = $this->_messageHelper->getGiftMessage($this->getEntity()->getGiftMessageId());

        // init default values for giftmessage form
        if (!$this->getMessage()->getSender()) {
            $this->getMessage()->setSender($this->getDefaultSender());
        }
        if (!$this->getMessage()->getRecipient()) {
            $this->getMessage()->setRecipient($this->getDefaultRecipient());
        }

        return $this;
    }

    /**
     * Retrieve gift message for entity
     *
     * @return \Magento\GiftMessage\Model\Message
     */
    public function getMessage()
    {
        if ($this->_giftMessage === null) {
            $this->_initMessage();
        }

        return $this->_giftMessage;
    }

    /**
     * Get save url
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'sales/order_view_giftmessage/save',
            ['entity' => $this->getEntity()->getId(), 'type' => 'order', 'reload' => 1]
        );
    }

    /**
     * Retrieve block html id
     *
     * @return string
     */
    public function getHtmlId()
    {
        return substr($this->getFieldIdPrefix(), 0, -1);
    }

    /**
     * Indicates that block can display giftmessages form
     *
     * @return bool
     */
    public function canDisplayGiftmessage()
    {
        return $this->_messageHelper->isMessagesAllowed(
            'order',
            $this->getEntity(),
            $this->getEntity()->getStoreId()
        );
    }
}
