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
 * Associated products collection
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Resource\Product\Type\Grouped;

class AssociatedProductsCollection
    extends \Magento\Catalog\Model\Resource\Product\Link\Product\Collection
{
    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Product types config
     *
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $_config;

    /**
     * Construct
     *
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
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     *
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
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_config = $config;
        parent::__construct($eventManager, $logger, $fetchStrategy, $entityFactory, $eavConfig, $coreResource,
            $eavEntityFactory, $universalFactory, $storeManager, $catalogData, $catalogProductFlat, $coreStoreConfig,
            $productOptionFactory, $catalogUrl, $locale, $customerSession, $resourceHelper
        );
    }

    /**
     * Retrieve currently edited product model
     *
     * @return \Magento\Catalog\Model\Product
     */
    protected function _getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @inheritdoc
     */
    public function _initSelect()
    {
        parent::_initSelect();

        $configData = $this->_config->getType('grouped');
        $allowProductTypes = isset($configData['allow_product_types']) ? $configData['allow_product_types'] : array();
        $this->setProduct($this->_getProduct())
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('sku')
            ->addFilterByRequiredOptions()
            ->addAttributeToFilter('type_id', $allowProductTypes);

        return $this;
    }
}
