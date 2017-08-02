<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

/**
 * Catalog view layer model
 *
 * @api
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Layer extends \Magento\Framework\DataObject
{
    /**
     * Product collections array
     *
     * @var array
     * @since 2.0.0
     */
    protected $_productCollections = [];

    /**
     * Key which can be used for load/save aggregation data
     *
     * @var string
     * @since 2.0.0
     */
    protected $_stateKey = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product
     * @since 2.0.0
     */
    protected $_catalogProduct;

    /**
     * Attribute collection factory
     *
     * @var AttributeCollectionFactory
     * @since 2.0.0
     */
    protected $_attributeCollectionFactory;

    /**
     * Layer state factory
     *
     * @var \Magento\Catalog\Model\Layer\StateFactory
     * @since 2.0.0
     */
    protected $_layerStateFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\ItemCollectionProviderInterface
     * @since 2.0.0
     */
    protected $collectionProvider;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\StateKey
     * @since 2.0.0
     */
    protected $stateKeyGenerator;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\CollectionFilter
     * @since 2.0.0
     */
    protected $collectionFilter;

    /**
     * @var CategoryRepositoryInterface
     * @since 2.0.0
     */
    protected $categoryRepository;

    /**
     * @param Layer\ContextInterface $context
     * @param Layer\StateFactory $layerStateFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $catalogProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Model\Layer\ContextInterface $context,
        \Magento\Catalog\Model\Layer\StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $catalogProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->_layerStateFactory = $layerStateFactory;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogProduct = $catalogProduct;
        $this->_storeManager = $storeManager;
        $this->registry = $registry;
        $this->categoryRepository = $categoryRepository;
        $this->collectionProvider = $context->getCollectionProvider();
        $this->stateKeyGenerator = $context->getStateKey();
        $this->collectionFilter = $context->getCollectionFilter();
        parent::__construct($data);
    }

    /**
     * Get layer state key
     *
     * @return string
     * @since 2.0.0
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
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @since 2.0.0
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\Layer
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getCurrentCategory()
    {
        $category = $this->getData('current_category');
        if ($category === null) {
            $category = $this->registry->registry('current_category');
            if ($category) {
                $this->setData('current_category', $category);
            } else {
                $category = $this->categoryRepository->get($this->getCurrentStore()->getRootCategoryId());
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function setCurrentCategory($category)
    {
        if (is_numeric($category)) {
            try {
                $category = $this->categoryRepository->get($category);
            } catch (NoSuchEntityException $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the category.'), $e);
            }
        } elseif ($category instanceof \Magento\Catalog\Model\Category) {
            if (!$category->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please correct the category.'));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Must be category model instance or its id.')
            );
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
     * @since 2.0.0
     */
    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Retrieve layer state object
     *
     * @return \Magento\Catalog\Model\Layer\State
     * @since 2.0.0
     */
    public function getState()
    {
        $state = $this->getData('state');
        if ($state === null) {
            \Magento\Framework\Profiler::start(__METHOD__);
            $state = $this->_layerStateFactory->create();
            $this->setData('state', $state);
            \Magento\Framework\Profiler::stop(__METHOD__);
        }

        return $state;
    }
}
