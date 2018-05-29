<?php

namespace Magento\CatalogSearch\Model\ResourceModel\Advanced;

/**
 * Factory class for @see \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
 */
class CollectionFactory
    extends \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
{
    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Magento\\CatalogSearch\\Model\\ResourceModel\\Advanced\\Collection')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
