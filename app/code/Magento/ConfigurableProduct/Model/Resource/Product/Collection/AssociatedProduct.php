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

/**
 * Catalog compare item resource model
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Collection;

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
     * @var \Magento\Registry
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
     * @param \Magento\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\App\Resource $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Registry $registryManager
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Zend_Db_Adapter_Abstract $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\App\Resource $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Registry $registryManager,
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
            $catalogData,
            $catalogProductFlatState,
            $coreStoreConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
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
            array('neq' => $this->getProduct()->getId())
        )->addFilterByRequiredOptions()->joinAttribute(
            'name',
            'catalog_product/name',
            'entity_id',
            null,
            'inner'
        )->joinTable(
            array('cisi' => 'cataloginventory_stock_item'),
            'product_id=entity_id',
            array('qty' => 'qty', 'inventory_in_stock' => 'is_in_stock'),
            null,
            'left'
        );

        return $this;
    }
}
