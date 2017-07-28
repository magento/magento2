<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Config\Backend;

/**
 * Config category field backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Category extends \Magento\Framework\App\Config\Value
{
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\Category
     * @since 2.0.0
     */
    protected $_catalogCategory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Catalog\Model\Category $catalogCategory
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
        \Magento\Catalog\Model\Category $catalogCategory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_catalogCategory = $catalogCategory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function afterSave()
    {
        if ($this->getScope() == 'stores') {
            $rootId = $this->getValue();
            $storeId = $this->getScopeId();

            $tree = $this->_catalogCategory->getTreeModel();

            // Create copy of categories attributes for choosed store
            $tree->load();
            $root = $tree->getNodeById($rootId);

            // Save root
            $this->_catalogCategory->setStoreId(0)->load($root->getId());
            $this->_catalogCategory->setStoreId($storeId)->save();

            foreach ($root->getAllChildNodes() as $node) {
                $this->_catalogCategory->setStoreId(0)->load($node->getId());
                $this->_catalogCategory->setStoreId($storeId)->save();
            }
        }
        return parent::afterSave();
    }
}
