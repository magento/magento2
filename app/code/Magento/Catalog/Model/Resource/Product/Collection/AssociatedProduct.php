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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog compare item resource model
 */
namespace Magento\Catalog\Model\Resource\Product\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AssociatedProduct
    extends \Magento\Catalog\Model\Resource\Product\Collection
{
    /**
     * Registry instance
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_registryManager;

    /**
     * Product type configurable instance
     *
     * @var \Magento\Catalog\Model\Product\Type\Configurable
     */
    protected $_productType;

    /**
     * Configuration helper instance
     *
     * @var \Magento\Catalog\Helper\Product\Configuration
     */
    protected $_configurationHelper;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Core\Model\Resource $coreResource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Catalog\Helper\Product\Flat $catalogProductFlat
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\Resource\Url $catalogUrl
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param \Magento\Core\Model\Registry $registryManager
     * @param \Magento\Catalog\Model\Product\Type\Configurable $productType
     * @param \Magento\Catalog\Helper\Product\Configuration $configurationHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Logger $logger,
        \Magento\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Core\Model\Resource $coreResource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Helper\Product\Flat $catalogProductFlat,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\Resource\Url $catalogUrl,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        \Magento\Core\Model\Registry $registryManager,
        \Magento\Catalog\Model\Product\Type\Configurable $productType,
        \Magento\Catalog\Helper\Product\Configuration $configurationHelper
    ) {
        $this->_registryManager = $registryManager;
        $this->_productType = $productType;
        $this->_configurationHelper = $configurationHelper;
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $eavConfig, $coreResource,
            $eavEntityFactory, $universalFactory, $storeManager, $catalogData, $catalogProductFlat, $coreStoreConfig,
            $productOptionFactory, $catalogUrl, $locale, $customerSession, $resourceHelper
        );
    }

    /**
     * Get product type
     *
     * @return \Magento\Catalog\Model\Product\Type\Configurable
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
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $allowedProductTypes = $this->_configurationHelper->getConfigurableAllowedTypes();

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('weight')
            ->addAttributeToSelect('image')
            ->addFieldToFilter('type_id', $allowedProductTypes)
            ->addFieldToFilter('entity_id', array('neq' => $this->getProduct()->getId()))
            ->addFilterByRequiredOptions()
            ->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner');

        return $this;
    }
}
