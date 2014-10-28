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
namespace Magento\Catalog\Model\Config\Backend;

/**
 * Config category field backend
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Category extends \Magento\Framework\App\Config\Value
{
    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Model\Category
     */
    protected $_catalogCategory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Model\Category $catalogCategory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Model\Category $catalogCategory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_catalogCategory = $catalogCategory;
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterSave()
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
        return $this;
    }
}
