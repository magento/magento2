<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog compare item resource model
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Collection;

use Magento\Customer\Api\GroupManagementInterface;

/**
 * Catalog compare item resource model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssociatedProduct extends \Magento\Catalog\Model\Resource\Product\Collection
{
    /**
     * Registry instance
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * Product type configurable instance
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_productType;

    /**
     * Product type configuration
     *
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $_productTypeConfig;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Framework\Registry $registryManager
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Zend_Db_Adapter_Abstract $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        GroupManagementInterface $groupManagement,
        \Magento\Framework\Registry $registryManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        $connection = null
    ) {
        $this->_registryManager = $registryManager;
        $this->_productType = $productType;
        $this->_productTypeConfig = $productTypeConfig;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
    }

    /**
     * Get product type
     *
     * @return \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    public function getProductType()
    {
        return $this->_productType;
    }

    /**
     * Retrieve currently edited product object
     *
     * @return mixed
     */
    private function getProduct()
    {
        return $this->_registryManager->registry('current_product');
    }

    /**
     * Add attributes to select
     *
     * @return $this
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $allowedProductTypes = $this->_productTypeConfig->getComposableTypes();

        $this->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'price'
        )->addAttributeToSelect(
            'sku'
        )->addAttributeToSelect(
            'weight'
        )->addAttributeToSelect(
            'image'
        )->addFieldToFilter(
            'type_id',
            $allowedProductTypes
        )->addFieldToFilter(
            'entity_id',
            ['neq' => $this->getProduct()->getId()]
        )->addFilterByRequiredOptions()->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner'
        )->joinTable(
            ['cisi' => 'cataloginventory_stock_item'],
            'product_id=entity_id',
            ['qty' => 'qty', 'inventory_in_stock' => 'is_in_stock'],
            null,
            'left'
        );

        return $this;
    }
}
