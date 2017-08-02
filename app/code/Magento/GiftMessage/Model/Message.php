<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

use Magento\Framework\Api\AttributeValueFactory;

/**
 * Gift Message model
 *
 * @api
 * @method \Magento\GiftMessage\Model\ResourceModel\Message _getResource()
 * @method \Magento\GiftMessage\Model\ResourceModel\Message getResource()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Message extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\GiftMessage\Api\Data\MessageInterface
{
    /**
     * @var \Magento\GiftMessage\Model\TypeFactory
     * @since 2.0.0
     */
    protected $_typeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param TypeFactory $typeFactory
     * @param \Magento\GiftMessage\Model\ResourceModel\Message $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\GiftMessage\Model\TypeFactory $typeFactory,
        \Magento\GiftMessage\Model\ResourceModel\Message $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\GiftMessage\Model\ResourceModel\Message::class);
    }

    /**
     * Return model from entity type
     *
     * @param string $type
     * @return mixed
     * @since 2.0.0
     */
    public function getEntityModelByType($type)
    {
        return $this->_typeFactory->createType($type);
    }

    /**
     * Checks if the gift message is empty
     *
     * @return bool
     * @since 2.0.0
     */
    public function isMessageEmpty()
    {
        return trim($this->getMessage()) == '';
    }

    //@codeCoverageIgnoreStart

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getGiftMessageId()
    {
        return $this->getData(self::GIFT_MESSAGE_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGiftMessageId($id)
    {
        return $this->setData(self::GIFT_MESSAGE_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setCustomerId($id)
    {
        return $this->setData(self::CUSTOMER_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSender()
    {
        return $this->getData(self::SENDER);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setSender($sender)
    {
        return $this->setData(self::SENDER, $sender);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRecipient()
    {
        return $this->getData(self::RECIPIENT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRecipient($recipient)
    {
        return $this->setData(self::RECIPIENT, $recipient);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\GiftMessage\Api\Data\MessageExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\GiftMessage\Api\Data\MessageExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\GiftMessage\Api\Data\MessageExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}
