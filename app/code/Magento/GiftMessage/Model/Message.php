<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Gift Message model
 *
 * @method \Magento\GiftMessage\Model\Resource\Message _getResource()
 * @method \Magento\GiftMessage\Model\Resource\Message getResource()
 * @method \Magento\GiftMessage\Model\Message setCustomerId(int $value)
 * @method \Magento\GiftMessage\Model\Message setSender(string $value)
 * @method \Magento\GiftMessage\Model\Message setRecipient(string $value)
 * @method \Magento\GiftMessage\Model\Message setMessage(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Message extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\GiftMessage\Api\Data\MessageInterface
{
    /**
     * @var \Magento\GiftMessage\Model\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param Resource\Message $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param TypeFactory $typeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\GiftMessage\Model\Resource\Message $resource,
        \Magento\Framework\Data\Collection\Db $resourceCollection,
        \Magento\GiftMessage\Model\TypeFactory $typeFactory,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\GiftMessage\Model\Resource\Message');
    }

    /**
     * Return model from entity type
     *
     * @param string $type
     * @return mixed
     */
    public function getEntityModelByType($type)
    {
        return $this->_typeFactory->createType($type);
    }

    /**
     * Checks thats gift message is empty
     *
     * @return bool
     */
    public function isMessageEmpty()
    {
        return trim($this->getMessage()) == '';
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getGiftMessageId()
    {
        return $this->getData('gift_message_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getSender()
    {
        return $this->getData('sender');
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipient()
    {
        return $this->getData('recipient');
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData('message');
    }
    ////@codeCoverageIgnoreEnd
}
