<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

use Magento\Framework\Data\Tree\Node;
use Magento\Store\Model\Store;

/**
 * Class AbstractCategory
 * @since 2.0.0
 */
class AbstractCategory extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\Tree
     * @since 2.0.0
     */
    protected $_categoryTree;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     * @since 2.0.0
     */
    protected $_categoryFactory;

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_withProductCount;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->_categoryTree = $categoryTree;
        $this->_coreRegistry = $registry;
        $this->_categoryFactory = $categoryFactory;
        $this->_withProductCount = true;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current category instance
     *
     * @return array|null
     * @since 2.0.0
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('category');
    }

    /**
     * @return int|string|null
     * @since 2.0.0
     */
    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return \Magento\Catalog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function hasStoreRootCategory()
    {
        $root = $this->getRoot();
        if ($root && $root->getId()) {
            return true;
        }
        return false;
    }

    /**
     * @return Store
     * @since 2.0.0
     */
    public function getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store');
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @param mixed|null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|array|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->_coreRegistry->registry('root');
        if ($root === null) {
            $storeId = (int)$this->getRequest()->getParam('store');

            if ($storeId) {
                $store = $this->_storeManager->getStore($storeId);
                $rootId = $store->getRootCategoryId();
            } else {
                $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            }

            $tree = $this->_categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $this->_coreRegistry->register('root', $root);
        }

        return $root;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    protected function _getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     * @since 2.0.0
     */
    public function getCategoryCollection()
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        $collection = $this->getData('category_collection');
        if ($collection === null) {
            $collection = $this->_categoryFactory->create()->getCollection();

            $collection->addAttributeToSelect(
                'name'
            )->addAttributeToSelect(
                'is_active'
            )->setProductStoreId(
                $storeId
            )->setLoadProductCount(
                $this->_withProductCount
            )->setStoreId(
                $storeId
            );

            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get and register categories root by specified categories IDs
     *
     * IDs can be arbitrary set of any categories ids.
     * Tree with minimal required nodes (all parents and neighbours) will be built.
     * If ids are empty, default tree with depth = 2 will be returned.
     *
     * @param array $ids
     * @return mixed
     * @since 2.0.0
     */
    public function getRootByIds($ids)
    {
        $root = $this->_coreRegistry->registry('root');
        if (null === $root) {
            $ids = $this->_categoryTree->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree = $this->_categoryTree->loadByIds($ids);
            $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->_coreRegistry->register('root', $root);
        }
        return $root;
    }

    /**
     * @param mixed $parentNodeCategory
     * @param int $recursionLevel
     * @return Node
     * @since 2.0.0
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->_categoryTree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->_categoryTree->addCollectionData($this->getCategoryCollection());

        return $node;
    }

    /**
     * @param array $args
     * @return string
     * @since 2.0.0
     */
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false, 'store' => $this->getStore()->getId()];
        $params = array_merge($params, $args);
        return $this->getUrl('catalog/*/save', $params);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'catalog/category/edit',
            ['store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * Return ids of root categories as array
     *
     * @return array
     * @since 2.0.0
     */
    public function getRootIds()
    {
        $ids = $this->getData('root_ids');
        if ($ids === null) {
            $ids = [\Magento\Catalog\Model\Category::TREE_ROOT_ID];
            foreach ($this->_storeManager->getGroups() as $store) {
                $ids[] = $store->getRootCategoryId();
            }
            $this->setData('root_ids', $ids);
        }
        return $ids;
    }
}
