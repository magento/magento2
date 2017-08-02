<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Model;

/**
 * Factory class for Eav Entity Types
 * @since 2.0.0
 */
class TypeFactory
{
    /**
     * Allowed types of entities for using of gift messages
     *
     * @var array
     * @since 2.0.0
     */
    protected $_allowedEntityTypes = [
        'order' => \Magento\Sales\Model\Order::class,
        'order_item' => \Magento\Sales\Model\Order\Item::class,
        'order_address' => \Magento\Sales\Model\Order\Address::class,
        'quote' => \Magento\Quote\Model\Quote::class,
        'quote_item' => \Magento\Quote\Model\Quote\Item::class,
        'quote_address' => \Magento\Quote\Model\Quote\Address::class,
        'quote_address_item' => \Magento\Quote\Model\Quote\Address\Item::class,
    ];

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create type object
     *
     * @param string $eavType
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function createType($eavType)
    {
        $types = $this->_allowedEntityTypes;
        if (!isset($types[$eavType])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown entity type'));
        }
        return $this->_objectManager->create($types[$eavType]);
    }
}
