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
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\CatalogSearch\Model;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Layer\Filter\Attribute as FilterAttribute;
use Magento\Catalog\Model\Layer\StateFactory;
use Magento\Catalog\Model\Layer as ModelLayer;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\Resource\Product\Attribute\Collection;
use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Catalog\Model\Resource\Product;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\Query;
use Magento\CatalogSearch\Model\Resource\Fulltext\CollectionFactory;
use Magento\Registry;
use Magento\Core\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Attribute;

class Layer extends ModelLayer
{
    const XML_PATH_DISPLAY_LAYER_COUNT = 'catalog/search/use_layered_navigation_count';

    /**
     * Catalog search data
     *
     * @var Data
     */
    protected $_catalogSearchData = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog config
     *
     * @var Config
     */
    protected $_catalogConfig;

    /**
     * Catalog product visibility
     *
     * @var Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Fulltext collection factory
     *
     * @var CollectionFactory
     */
    protected $_fulltextCollectionFactory;

    /**
     * Construct
     *
     * @param StateFactory $layerStateFactory
     * @param CategoryFactory $categoryFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param Product $catalogProduct
     * @param StoreManagerInterface $storeManager
     * @param Visibility $catalogProductVisibility
     * @param Config $catalogConfig
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param CollectionFactory $fulltextCollectionFactory
     * @param Data $catalogSearchData
     * @param array $data
     */
    public function __construct(
        StateFactory $layerStateFactory,
        CategoryFactory $categoryFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        Product $catalogProduct,
        StoreManagerInterface $storeManager,
        Visibility $catalogProductVisibility,
        Config $catalogConfig,
        Session $customerSession,
        Registry $coreRegistry,
        CollectionFactory $fulltextCollectionFactory,
        Data $catalogSearchData,
        array $data = array()
    ) {
        $this->_fulltextCollectionFactory = $fulltextCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig = $catalogConfig;
        $this->_storeManager = $storeManager;
        $this->_catalogSearchData = $catalogSearchData;
        parent::__construct($layerStateFactory, $categoryFactory, $attributeCollectionFactory, $catalogProduct,
            $storeManager, $catalogProductVisibility, $catalogConfig, $customerSession, $coreRegistry);
    }

    /**
     * Get current layer product collection
     *
     * @return Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->_fulltextCollectionFactory->create();
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }
        return $collection;
    }

    /**
     * Prepare product collection
     *
     * @param Collection $collection
     * @return $this
     */
    public function prepareProductCollection($collection)
    {
        $collection
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addSearchFilter($this->_catalogSearchData->getQuery()->getQueryText())
            ->setStore($this->_storeManager->getStore())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addStoreFilter()
            ->addUrlRewrite()
            ->setVisibility($this->_catalogProductVisibility->getVisibleInSearchIds());

        return $this;
    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if ($this->_stateKey === null) {
            $this->_stateKey = 'Q_' . $this->_catalogSearchData->getQuery()->getId()
                . '_'. parent::getStateKey();
        }
        return $this->_stateKey;
    }

    /**
     * Get default tags for current layer state
     *
     * @param   array $additionalTags
     * @return  array
     */
    public function getStateTags(array $additionalTags = array())
    {
        $additionalTags = parent::getStateTags($additionalTags);
        $additionalTags[] = Query::CACHE_TAG;
        return $additionalTags;
    }

    /**
     * Add filters to attribute collection
     *
     * @param   Collection $collection
     * @return  Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addIsFilterableInSearchFilter()
            ->addVisibleFilter();
        return $collection;
    }

    /**
     * Prepare attribute for use in layered navigation
     *
     * @param   Attribute $attribute
     * @return  Attribute
     */
    protected function _prepareAttribute($attribute)
    {
        $attribute = parent::_prepareAttribute($attribute);
        $attribute->setIsFilterable(FilterAttribute::OPTIONS_ONLY_WITH_RESULTS);
        return $attribute;
    }
}
