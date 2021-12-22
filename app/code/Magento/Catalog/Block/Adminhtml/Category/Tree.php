<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Categories tree block
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Store\Model\Store;

/**
 * Class Category Tree
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 * @since 100.0.2
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/category/tree.phtml';

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var SecureHtmlRenderer
     */
    protected $secureRenderer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Backend\Model\Auth\Session $backendSession,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_resourceHelper = $resourceHelper;
        $this->_backendSession = $backendSession;
        parent::__construct($context, $categoryTree, $registry, $categoryFactory, $data);
        $this->secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(0);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $addUrl = $this->getUrl("*/*/add", ['_current' => false, 'id' => null, '_query' => false]);
        if ($this->getStore()->getId() == Store::DEFAULT_STORE_ID) {
            $this->addChild(
                'add_sub_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Add Subcategory'),
                    'onclick' => "addNew('" . $addUrl . "', false)",
                    'class' => 'add',
                    'id' => 'add_subcategory_button',
                    'style' => $this->canAddSubCategory() ? '' : 'display: none;'
                ]
            );

            if ($this->canAddRootCategory()) {
                $this->addChild(
                    'add_root_button',
                    \Magento\Backend\Block\Widget\Button::class,
                    [
                        'label' => __('Add Root Category'),
                        'onclick' => "addNew('" . $addUrl . "', true)",
                        'class' => 'add',
                        'id' => 'add_root_category_button'
                    ]
                );
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve list of categories with name containing $namePart and their parents
     *
     * @param string $namePart
     * @return string
     */
    public function getSuggestedCategoriesJson($namePart)
    {
        $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());

        /* @var $collection Collection */
        $collection = $this->_categoryFactory->create()->getCollection();

        $matchingNamesCollection = clone $collection;
        $escapedNamePart = $this->_resourceHelper->addLikeEscape(
            $namePart,
            ['position' => 'any']
        );
        $matchingNamesCollection->addAttributeToFilter(
            'name',
            ['like' => $escapedNamePart]
        )->addAttributeToFilter(
            'entity_id',
            ['neq' => \Magento\Catalog\Model\Category::TREE_ROOT_ID]
        )->addAttributeToSelect(
            'path'
        )->setStoreId(
            $storeId
        );

        $shownCategoriesIds = [];
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        $collection->addAttributeToFilter(
            'entity_id',
            ['in' => array_keys($shownCategoriesIds)]
        )->addAttributeToSelect(
            ['name', 'is_active', 'parent_id']
        )->setStoreId(
            $storeId
        );

        $categoryById = [
            \Magento\Catalog\Model\Category::TREE_ROOT_ID => [
                'id' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getParentId()]['children'][] = & $categoryById[$category->getId()];
        }

        return $this->_jsonEncoder->encode($categoryById[\Magento\Catalog\Model\Category::TREE_ROOT_ID]['children']);
    }

    /**
     * Get add root button html
     *
     * @return string
     */
    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }

    /**
     * Get add sub button html
     *
     * @return string
     */
    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    /**
     * Get expand button html
     *
     * @return string
     */
    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }

    /**
     * Get collapse button html
     *
     * @return string
     */
    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }

    /**
     * Get store switcher
     *
     * @return string
     */
    public function getStoreSwitcherHtml()
    {
        return $this->getChildHtml('store_switcher');
    }

    /**
     * Get loader tree url
     *
     * @param bool|null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        $params = ['_current' => true, 'id' => null, 'store' => null];
        if ($expanded === null && $this->_backendSession->getIsTreeWasExpanded() || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/categoriesJson', $params);
    }

    /**
     * Get nodes url
     *
     * @return string
     */
    public function getNodesUrl()
    {
        return $this->getUrl('catalog/category/tree');
    }

    /**
     * Get switcher tree url
     *
     * @return string
     */
    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            'catalog/category/tree',
            ['_current' => true, 'store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * Get is was expanded
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsWasExpanded()
    {
        return $this->_backendSession->getIsTreeWasExpanded();
    }

    /**
     * Get move url
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('catalog/category/move', ['store' => $this->getRequest()->getParam('store')]);
    }

    /**
     * Get tree
     *
     * @param mixed|null $parenNodeCategory
     * @return array
     */
    public function getTree($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : [];
        return $tree;
    }

    /**
     * Get tree json
     *
     * @param mixed|null $parenNodeCategory
     * @return string
     */
    public function getTreeJson($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $json = $this->_jsonEncoder->encode(isset($rootArray['children']) ? $rootArray['children'] : []);
        return $json;
    }

    /**
     * Get JSON of array of categories, that are breadcrumbs for specified category path
     *
     * @param string $path
     * @param string $javascriptVarName
     * @return string
     */
    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }

        $categories = $this->_categoryTree->setStoreId($this->getStore()->getId())->loadBreadcrumbsArray($path);
        if (empty($categories)) {
            return '';
        }
        foreach ($categories as $key => $category) {
            $categories[$key] = $this->_getNodeJson($category);
        }
        $scriptString = 'require(["prototype"], function(){' . $javascriptVarName . ' = ' . $this->_jsonEncoder->encode(
            $categories
        ) .
            ';' .
            ($this->canAddSubCategory() ? '$("add_subcategory_button").show();' : '$("add_subcategory_button").hide();')
            . '});';

        return /* @noEscape */ $this->secureRenderer->renderTag('script', [], $scriptString, false);
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Node($node, 'entity_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->buildNodeName($node);

        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id'] = $node->getId();
        $item['store'] = (int)$this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && ($node->getLevel() == 1 && $rootForStores ? false : true);

        if ((int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }

        $isParent = $this->_isParentSelectedCategory($node);

        if ($node->hasChildren()) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 1) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Get category name
     *
     * @param \Magento\Framework\DataObject $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        $result .= ' (ID: ' . $node->getId() . ')';
        if ($this->_withProductCount) {
            $result .= ' (' . $node->getProductCount() . ')';
        }
        return $result;
    }

    /**
     * Is category movable
     *
     * @param Node|array $node
     * @return bool
     */
    protected function _isCategoryMoveable($node)
    {
        $options = new \Magento\Framework\DataObject(['is_moveable' => true, 'category' => $node]);

        $this->_eventManager->dispatch('adminhtml_catalog_category_tree_is_moveable', ['options' => $options]);

        return $options->getIsMoveable();
    }

    /**
     * Is parent selected category
     *
     * @param Node|array $node
     * @return bool
     */
    protected function _isParentSelectedCategory($node)
    {
        if ($node && $this->getCategory()) {
            $pathIds = $this->getCategory()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if page loaded by outside link to category edit
     *
     * @return boolean
     */
    public function isClearEdit()
    {
        return (bool)$this->getRequest()->getParam('clear');
    }

    /**
     * Check availability of adding root category
     *
     * @return boolean
     */
    public function canAddRootCategory()
    {
        $options = new \Magento\Framework\DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_catalog_category_tree_can_add_root_category',
            ['category' => $this->getCategory(), 'options' => $options, 'store' => $this->getStore()->getId()]
        );

        return $options->getIsAllow();
    }

    /**
     * Check availability of adding sub category
     *
     * @return boolean
     */
    public function canAddSubCategory()
    {
        $options = new \Magento\Framework\DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_catalog_category_tree_can_add_sub_category',
            ['category' => $this->getCategory(), 'options' => $options, 'store' => $this->getStore()->getId()]
        );

        return $options->getIsAllow();
    }
}
