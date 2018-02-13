<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Block\Catalog\Category;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Helper\Data;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

/**
 * Categories tree block for URL rewrites editing process
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Tree extends AbstractCategory
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
     * @var Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param Registry $registry
     * @param EncoderInterface $jsonEncoder
     * @param ProductFactory $productFactory
     * @param CategoryFactory $categoryFactory
     * @param Data $adminhtmlData
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        Registry $registry,
        CategoryFactory $categoryFactory,
        EncoderInterface $jsonEncoder,
        ProductFactory $productFactory,
        Data $adminhtmlData,
        CategoryRepositoryInterface $categoryRepository,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_categoryFactory = $categoryFactory;
        $this->_productFactory = $productFactory;
        $this->_adminhtmlData = $adminhtmlData;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
        $this->categoryRepository = $categoryRepository;
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

        $result = [];
        if ($parentId) {
            try {
                $category = $this->categoryRepository->get($parentId);
            } catch (NoSuchEntityException $e) {
                $category = null;
            }
            if ($category) {
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
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->_getData('category_collection');
        if ($collection === null) {
            $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
                ['name', 'is_active']
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
        $result = [
            'id' => (int)$node->getId(),
            'parent_id' => (int)$node->getParentId(),
            'children_count' => (int)$node->getChildrenCount(),
            'is_active' => (bool)$node->getIsActive(),
            // Scrub names for raw js output
            'name' => $this->escapeHtml($node->getName()),
            'level' => (int)$node->getLevel(),
            'product_count' => (int)$node->getProductCount(),
        ];

        if ($node->getParentId() == Category::TREE_ROOT_ID && !in_array($result['id'], $this->_allowedCategoryIds)) {
            $result['disabled'] = true;
        }

        if ($node->hasChildren()) {
            $result['children'] = [];
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
