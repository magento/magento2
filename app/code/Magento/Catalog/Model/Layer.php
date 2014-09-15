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
namespace Magento\Catalog\Model;

/**
 * Catalog view layer model
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Layer extends \Magento\Framework\Object
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
     * @var \Magento\Framework\Registry
     */
    protected $registry = null;

    /**
     * Store manager
     *
     * @var \Magento\Framework\StoreManagerInterface
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
     * @var \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
     */
    protected $collectionProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\StateKey
     */
    protected $stateKeyGenerator;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter
     */
    protected $collectionFilter;

    /**
     * @param Layer\ContextInterface $context
     * @param Layer\StateFactory $layerStateFactory
     * @param CategoryFactory $categoryFactory
     * @param Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param Resource\Product $catalogProduct
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\Resource\Product $catalogProduct,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_layerStateFactory = $layerStateFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->collectionProvider = $context->getCollectionProvider();
        $this->stateKeyGenerator = $context->getStateKey();
        $this->collectionFilter = $context->getCollectionFilter();
        parent::__construct($data);
    }

    /**
     * Get layer state key
     *
     * @return string
     */
    public function getStateKey()
    {
        if (!$this->_stateKey) {
            $this->_stateKey = $this->stateKeyGenerator->toString($this->getCurrentCategory());
        }
        return $this->_stateKey;
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
            $collection = $this->collectionProvider->getCollection($this->getCurrentCategory());
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
        $this->collectionFilter->filter($collection, $this->getCurrentCategory());

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
            $stateSuffix .= '_' . $filterItem->getFilter()->getRequestVar() . '_' . $filterItem->getValueString();
        }
        if (!empty($stateSuffix)) {
            $this->_stateKey = $this->getStateKey() . $stateSuffix;
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
            $category = $this->registry->registry('current_category');
            if ($category) {
                $this->setData('current_category', $category);
            } else {
                /** @var \Magento\Catalog\Model\Category $category */
                $category = $this->_categoryFactory->create()->load($this->getCurrentStore()->getRootCategoryId());
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function setCurrentCategory($category)
    {
        if (is_numeric($category)) {
            $category = $this->_categoryFactory->create()->load($category);
        }
        if (!$category instanceof \Magento\Catalog\Model\Category) {
            throw new \Magento\Framework\Model\Exception(
                __('The category must be an instance of \Magento\Catalog\Model\Category.')
            );
        }
        if (!$category->getId()) {
            throw new \Magento\Framework\Model\Exception(__('Please correct the category.'));
        }

        if ($category->getId() != $this->getCurrentCategory()->getId()) {
            $this->setData('current_category', $category);
        }

        return $this;
    }

    /**
     * Retrieve current store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
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
            \Magento\Framework\Profiler::start(__METHOD__);
            $state = $this->_layerStateFactory->create();
            $this->setData('state', $state);
            \Magento\Framework\Profiler::stop(__METHOD__);
        }

        return $state;
    }
}
