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
namespace Magento\UrlRewrite\Block\Catalog\Category;

use Magento\Catalog\Model\Category;

/**
 * Categories tree block for URL rewrites editing process
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory
{
    /**
     * List of allowed category ids
     *
     * @var []
     */
    protected $_allowedCategoryIds = [];

    /**
     * @var string
     */
    protected $_template = 'categories.phtml';

    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Catalog\Model\Resource\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Resource\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Backend\Helper\Data $adminhtmlData,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_adminhtmlData = $adminhtmlData;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
    }

    /**
     * Get categories tree as recursive array
     *
     * @param int $parentId
     * @param bool $asJson
     * @param int $recursionLevel
     * @return array
     */
    public function getTreeArray($parentId = null, $asJson = false, $recursionLevel = 3)
    {
        $productId = $this->_request->getParam('product');
        if ($productId) {
            $product = $this->_productFactory->create()->setId($productId);
            $this->_allowedCategoryIds = $product->getCategoryIds();
            unset($product);
        }

        $result = array();
        if ($parentId) {
            $category = $this->_categoryFactory->create()->load($parentId);
            if (!empty($category)) {
                $tree = $this->_getNodesArray($this->getNode($category, $recursionLevel));
                if (!empty($tree) && !empty($tree['children'])) {
                    $result = $tree['children'];
                }
            }
        } else {
            $result = $this->_getNodesArray($this->getRoot(null, $recursionLevel));
        }

        if ($asJson) {
            return $this->_jsonEncoder->encode($result);
        }

        $this->_allowedCategoryIds = [];

        return $result;
    }

    /**
     * Get categories collection
     *
     * @return \Magento\Catalog\Model\Resource\Category\Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->_getData('category_collection');
        if (is_null($collection)) {
            $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
                array('name', 'is_active')
            )->setLoadProductCount(
                true
            );
            $this->setData('category_collection', $collection);
        }

        return $collection;
    }

    /**
     * Convert categories tree to array recursively
     *
     * @param  \Magento\Framework\Data\Tree\Node $node
     * @return array
     */
    protected function _getNodesArray($node)
    {
        $result = array(
            'id' => (int)$node->getId(),
            'parent_id' => (int)$node->getParentId(),
            'children_count' => (int)$node->getChildrenCount(),
            'is_active' => (bool)$node->getIsActive(),
            'name' => $node->getName(),
            'level' => (int)$node->getLevel(),
            'product_count' => (int)$node->getProductCount()
        );

        if ($node->getParentId() == Category::TREE_ROOT_ID && !in_array($result['id'], $this->_allowedCategoryIds)) {
            $result['disabled'] = true;
        }

        if ($node->hasChildren()) {
            $result['children'] = array();
            foreach ($node->getChildren() as $childNode) {
                $result['children'][] = $this->_getNodesArray($childNode);
            }
        }
        $result['cls'] = ($result['is_active'] ? '' : 'no-') . 'active-category';
        $result['expanded'] = !empty($result['children']);

        return $result;
    }

    /**
     * Get URL for categories tree ajax loader
     *
     * @return string
     */
    public function getLoadTreeUrl()
    {
        return $this->_adminhtmlData->getUrl('adminhtml/*/categoriesJson');
    }
}
