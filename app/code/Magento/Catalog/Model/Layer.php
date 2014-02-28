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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model;

/**
 * Catalog view layer model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Layer extends \Magento\Object
{
    /**
     * Product collections array
     *
     * @var array
     */
    protected $_productCollections = array();

    /**
     * Key which can be used for load/save aggregation data
     *
     * @var string
     */
    protected $_stateKey = null;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\Resource\Product
     */
    protected $_catalogProduct;

    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * Category factory
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Layer state factory
     *
     * @var \Magento\Catalog\Model\Layer\StateFactory
     */
    protected $_layerStateFactory;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\Resource\Product $catalogProduct
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\Resource\Product $catalogProduct,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_layerStateFactory = $layerStateFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig = $catalogConfig;
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($data);
    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if ($this->_stateKey === null) {
            $this->_stateKey = 'STORE_' . $this->_storeManager->getStore()->getId()
                . '_CAT_' . $this->getCurrentCategory()->getId()
                . '_CUSTGROUP_' . $this->_customerSession->getCustomerGroupId();
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
        $additionalTags = array_merge($additionalTags, array(
            \Magento\Catalog\Model\Category::CACHE_TAG.$this->getCurrentCategory()->getId()
        ));

        return $additionalTags;
    }

    /**
     * Retrieve current layer product collection
     *
     * @return \Magento\Catalog\Model\Resource\Product\Collection
     */
    public function getProductCollection()
    {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            $collection = $this->getCurrentCategory()->getProductCollection();
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }

        return $collection;
    }

    /**
     * Initialize product collection
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @return \Magento\Catalog\Model\Layer
     */
    public function prepareProductCollection($collection)
    {
        $collection
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($this->getCurrentCategory()->getId())
            ->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        return $this;
    }

    /**
     * Apply layer
     * Method is colling after apply all filters, can be used
     * for prepare some index data before getting information
     * about existing intexes
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function apply()
    {
        $stateSuffix = '';
        foreach ($this->getState()->getFilters() as $filterItem) {
            $stateSuffix .= '_' . $filterItem->getFilter()->getRequestVar()
                . '_' . $filterItem->getValueString();
        }
        if (!empty($stateSuffix)) {
            $this->_stateKey = $this->getStateKey().$stateSuffix;
        }

        return $this;
    }

    /**
     * Retrieve current category model
     * If no category found in registry, the root will be taken
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCurrentCategory()
    {
        $category = $this->getData('current_category');
        if (is_null($category)) {
            $category = $this->_coreRegistry->registry('current_category');
            if ($category) {
                $this->setData('current_category', $category);
            } else {
                /** @var \Magento\Catalog\Model\Category $category */
                $category = $this->_categoryFactory->create()
                    ->load($this->getCurrentStore()->getRootCategoryId());
                $this->setData('current_category', $category);
            }
        }

        return $category;
    }

    /**
     * Change current category object
     *
     * @param mixed $category
     * @return \Magento\Catalog\Model\Layer
     * @throws \Magento\Core\Exception
     */
    public function setCurrentCategory($category)
    {
        if (is_numeric($category)) {
            $category = $this->_categoryFactory->create()->load($category);
        }
        if (!$category instanceof \Magento\Catalog\Model\Category) {
            throw new \Magento\Core\Exception(__('The category must be an instance of \Magento\Catalog\Model\Category.'));
        }
        if (!$category->getId()) {
            throw new \Magento\Core\Exception(__('Please correct the category.'));
        }

        if ($category->getId() != $this->getCurrentCategory()->getId()) {
            $this->setData('current_category', $category);
        }

        return $this;
    }

    /**
     * Retrieve current store model
     *
     * @return \Magento\Core\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Get collection of all filterable attributes for layer products set
     *
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getFilterableAttributes()
    {
        $setIds = $this->_getSetIds();
        if (!$setIds) {
            return array();
        }
        /** @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->_attributeCollectionFactory->create();
        $collection->setItemObjectClass('Magento\Catalog\Model\Resource\Eav\Attribute')
            ->setAttributeSetFilter($setIds)
            ->addStoreLabel($this->_storeManager->getStore()->getId())
            ->setOrder('position', 'ASC');
        $collection = $this->_prepareAttributeCollection($collection);
        $collection->load();

        return $collection;
    }

    /**
     * Prepare attribute for use in layered navigation
     *
     * @param   \Magento\Eav\Model\Entity\Attribute $attribute
     * @return  \Magento\Eav\Model\Entity\Attribute
     */
    protected function _prepareAttribute($attribute)
    {
        $this->_catalogProduct->getAttribute($attribute);
        return $attribute;
    }

    /**
     * Add filters to attribute collection
     *
     * @param   \Magento\Catalog\Model\Resource\Attribute\Collection $collection
     * @return  \Magento\Catalog\Model\Resource\Attribute\Collection
     */
    protected function _prepareAttributeCollection($collection)
    {
        $collection->addIsFilterableFilter();
        return $collection;
    }

    /**
     * Retrieve layer state object
     *
     * @return \Magento\Catalog\Model\Layer\State
     */
    public function getState()
    {
        $state = $this->getData('state');
        if (is_null($state)) {
            \Magento\Profiler::start(__METHOD__);
            $state = $this->_layerStateFactory->create();
            $this->setData('state', $state);
            \Magento\Profiler::stop(__METHOD__);
        }

        return $state;
    }

    /**
     * Get attribute sets identifiers of current product set
     *
     * @return array
     */
    protected function _getSetIds()
    {
        return $this->getProductCollection()->getSetIds();
    }
}
