<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GiftMessage\Model;

/**
 * Factory class for Eav Entity Types
 */
class TypeFactory
{
    /**
     * Allowed types of entities for using of gift messages
     *
     * @var array
     */
    protected $_allowedEntityTypes = [
        'order' => 'Magento\Sales\Model\Order',
        'order_item' => 'Magento\Sales\Model\Order\Item',
        'order_address' => 'Magento\Sales\Model\Order\Address',
        'quote' => 'Magento\Sales\Model\Quote',
        'quote_item' => 'Magento\Sales\Model\Quote\Item',
        'quote_address' => 'Magento\Sales\Model\Quote\Address',
        'quote_address_item' => 'Magento\Sales\Model\Quote\Address\Item',
    ];

    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function createType($eavType)
    {
        $types = $this->_allowedEntityTypes;
        if (!isset($types[$eavType])) {
            throw new \Magento\Framework\Model\Exception(__('Unknown entity type'));
        }
        return $this->_objectManager->create($types[$eavType]);
    }
}
