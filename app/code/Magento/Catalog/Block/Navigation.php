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
namespace Magento\Catalog\Block;

use Magento\Catalog\Model\Category;

/**
 * Catalog navigation
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Navigation extends \Magento\Framework\View\Element\Template implements \Magento\Framework\View\Block\IdentityInterface
{
    /**
     * @var Category
     */
    protected $_categoryInstance;

    /**
     * Current category key
     *
     * @var string
     */
    protected $_currentCategoryKey;

    /**
     * Array of level position counters
     *
     * @var array
     */
    protected $_itemLevelPositions = array();

    /**
     * Catalog category
     *
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_catalogCategory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer\Category
     */
    protected $_catalogLayer;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Category\Flat\State
     */
    protected $flatState;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Layer\Category $catalogLayer
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Category $catalogCategory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\Resource\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Category $catalogLayer,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Category $catalogCategory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $flatState,
        array $data = array()
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogLayer = $catalogLayer;
        $this->httpContext = $httpContext;
        $this->_catalogCategory = $catalogCategory;
        $this->_registry = $registry;
        $this->flatState = $flatState;
        $this->_categoryInstance = $categoryFactory->create();
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->addData(
            array(
                'cache_lifetime' => false,
                'cache_tags' => array(Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG)
            )
        );
    }

    /**
     * Get current category
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $shortCacheId = array(
            'CATALOG_NAVIGATION',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Helper\Data::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrenCategoryKey()
        );
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrenCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Get current category key
     *
     * @return string
     */
    public function getCurrenCategoryKey()
    {
        if (!$this->_currentCategoryKey) {
            $category = $this->_registry->registry('current_category');
            if ($category) {
                $this->_currentCategoryKey = $category->getPath();
            } else {
                $this->_currentCategoryKey = $this->_storeManager->getStore()->getRootCategoryId();
            }
        }

        return $this->_currentCategoryKey;
    }

    /**
     * Get catagories of current store
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
    public function getStoreCategories()
    {
        return $this->_catalogCategory->getStoreCategories();
    }

    /**
     * Retrieve child categories of current category
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
    public function getCurrentChildCategories()
    {
        $categories = $this->_catalogLayer->getCurrentCategory()->getChildrenCategories();
        /** @var \Magento\Catalog\Model\Resource\Product\Collection $productCollection */
        $productCollection = $this->_productCollectionFactory->create();
        $this->_catalogLayer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($categories);
        return $categories;
    }

    /**
     * Checkin activity of category
     *
     * @param   \Magento\Framework\Object $category
     * @return  bool
     */
    public function isCategoryActive($category)
    {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
        }
        return false;
    }

    /**
     * Get url for category data
     *
     * @param Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof Category) {
            $url = $category->getUrl();
        } else {
            $url = $this->_categoryInstance->setData($category->getData())->getUrl();
        }

        return $url;
    }

    /**
     * Return item position representation in menu tree
     *
     * @param int $level
     * @return string
     */
    protected function _getItemPosition($level)
    {
        if ($level == 0) {
            $zeroLevelPosition = isset(
                $this->_itemLevelPositions[$level]
            ) ? $this->_itemLevelPositions[$level] + 1 : 1;
            $this->_itemLevelPositions = array();
            $this->_itemLevelPositions[$level] = $zeroLevelPosition;
        } elseif (isset($this->_itemLevelPositions[$level])) {
            $this->_itemLevelPositions[$level]++;
        } else {
            $this->_itemLevelPositions[$level] = 1;
        }

        $position = array();
        for ($i = 0; $i <= $level; $i++) {
            if (isset($this->_itemLevelPositions[$i])) {
                $position[] = $this->_itemLevelPositions[$i];
            }
        }
        return implode('-', $position);
    }

    /**
     * Render category to html
     *
     * @param Category $category
     * @param int $level Nesting level number
     * @param boolean $isLast Whether ot not this item is last, affects list item class
     * @param boolean $isFirst Whether ot not this item is first, affects list item class
     * @param boolean $isOutermost Whether ot not this item is outermost, affects list item class
     * @param string $outermostItemClass Extra class of outermost list items
     * @param string $childrenWrapClass If specified wraps children list in div with this class
     * @param boolean $noEventAttributes Whether ot not to add on* attributes to list item
     * @return string
     */
    protected function _renderCategoryMenuItemHtml(
        $category,
        $level = 0,
        $isLast = false,
        $isFirst = false,
        $isOutermost = false,
        $outermostItemClass = '',
        $childrenWrapClass = '',
        $noEventAttributes = false
    ) {
        if (!$category->getIsActive()) {
            return '';
        }

        // get all children
        if ($this->flatState->isAvailable()) {
            $children = (array)$category->getChildrenNodes();
        } else {
            $children = $category->getChildren();
        }

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }

        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = $activeChildrenCount > 0;

        // prepare list item html classes
        $classes = array();
        $classes[] = 'level' . $level;
        $classes[] = 'nav-' . $this->_getItemPosition($level);
        if ($this->isCategoryActive($category)) {
            $classes[] = 'active';
        }

        $linkClass = '';
        if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="' . $outermostItemClass . '"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }
        if ($isLast) {
            $classes[] = 'last';
        }
        if ($hasActiveChildren) {
            $classes[] = 'parent';
        }

        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }
        if ($hasActiveChildren && !$noEventAttributes) {
            $attributes['onmouseover'] = 'toggleMenu(this,1)';
            $attributes['onmouseout'] = 'toggleMenu(this,0)';
        }

        // assemble list item with attributes
        $htmlLi = '<li';
        foreach ($attributes as $attrName => $attrValue) {
            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }
        $htmlLi .= '>';

        $html = array();
        $html[] = $htmlLi;

        $html[] = '<a href="' . $this->getCategoryUrl($category) . '"' . $linkClass . '>';
        $html[] = '<span>' . $this->escapeHtml($category->getName()) . '</span>';
        $html[] = '</a>';

        // render children
        $htmlChildren = '';
        $j = 0;
        foreach ($activeChildren as $child) {
            $htmlChildren .= $this->_renderCategoryMenuItemHtml(
                $child,
                $level + 1,
                $j == $activeChildrenCount - 1,
                $j == 0,
                false,
                $outermostItemClass,
                $childrenWrapClass,
                $noEventAttributes
            );
            $j++;
        }
        if (!empty($htmlChildren)) {
            if ($childrenWrapClass) {
                $html[] = '<div class="' . $childrenWrapClass . '">';
            }
            $html[] = '<ul class="level' . $level . '">';
            $html[] = $htmlChildren;
            $html[] = '</ul>';
            if ($childrenWrapClass) {
                $html[] = '</div>';
            }
        }

        $html[] = '</li>';

        $html = implode("\n", $html);
        return $html;
    }

    /**
     * Enter description here...
     *
     * @return Category
     */
    public function getCurrentCategory()
    {
        return $this->_catalogLayer->getCurrentCategory();
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCurrentCategoryPath()
    {
        if ($this->getCurrentCategory()) {
            return explode(',', $this->getCurrentCategory()->getPathInStore());
        }
        return array();
    }

    /**
     * Enter description here...
     *
     * @param Category $category
     * @return string
     */
    public function drawOpenCategoryItem($category)
    {
        $html = '';
        if (!$category->getIsActive()) {
            return $html;
        }

        $html .= '<li';

        if ($this->isCategoryActive($category)) {
            $html .= ' class="active"';
        }

        $html .= '>' . "\n";
        $html .= '<a href="' . $this->getCategoryUrl(
            $category
        ) . '">' . '<span>' . $this->escapeHtml(
            $category->getName()
        ) . '</span></a>' . "\n";

        if (in_array($category->getId(), $this->getCurrentCategoryPath())) {
            $children = $category->getChildren();
            $hasChildren = $children && $children->count();

            if ($hasChildren) {
                $htmlChildren = '';
                foreach ($children as $child) {
                    $htmlChildren .= $this->drawOpenCategoryItem($child);
                }

                if (!empty($htmlChildren)) {
                    $html .= '<ul>' . "\n" . $htmlChildren . '</ul>';
                }
            }
        }
        $html .= '</li>' . "\n";

        return $html;
    }

    /**
     * Render categories menu in HTML
     *
     * @param int $level Level number for list item class to start from
     * @param string $outermostItemClass Extra class of outermost list items
     * @param string $childrenWrapClass If specified wraps children list in div with this class
     * @return string
     */
    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '')
    {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = $activeCategoriesCount > 0;

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;
        foreach ($activeCategories as $category) {
            $html .= $this->_renderCategoryMenuItemHtml(
                $category,
                $level,
                $j == $activeCategoriesCount - 1,
                $j == 0,
                true,
                $outermostItemClass,
                $childrenWrapClass,
                true
            );
            $j++;
        }

        return $html;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return array(\Magento\Catalog\Model\Category::CACHE_TAG, \Magento\Store\Model\Group::CACHE_TAG);
    }
}
