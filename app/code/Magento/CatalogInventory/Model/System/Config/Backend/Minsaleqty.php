<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model\System\Config\Backend;

/**
 * Backend for serialized array data
 * @since 2.0.0
 */
class Minsaleqty extends \Magento\Framework\App\Config\Value
{
    /**
     * Catalog inventory minsaleqty
     *
     * @var \Magento\CatalogInventory\Helper\Minsaleqty
     * @since 2.0.0
     */
    protected $_catalogInventoryMinsaleqty = null;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\CatalogInventory\Helper\Minsaleqty $catalogInventoryMinsaleqty
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\CatalogInventory\Helper\Minsaleqty $catalogInventoryMinsaleqty,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_catalogInventoryMinsaleqty = $catalogInventoryMinsaleqty;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     *
     * @return void
     * @since 2.0.0
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->_catalogInventoryMinsaleqty->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     *
     * @return void
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->_catalogInventoryMinsaleqty->makeStorableArrayFieldValue($value);
        $this->setValue($value);
    }
}
