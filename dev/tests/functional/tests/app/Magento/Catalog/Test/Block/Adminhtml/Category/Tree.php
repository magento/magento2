<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class Tree
 * Categories tree block
 */
class Tree extends Block
{
    /**
     * 'Add Subcategory' button
     *
     * @var string
     */
    protected $addSubcategory = '#add_subcategory_button';

    /**
     * 'Add Root Category' button
     *
     * @var string
     */
    protected $addRootCategory = '#add_root_category_button';

    /**
     * 'Expand All' link
     *
     * @var string
     */
    protected $expandAll = 'a[onclick*=expandTree]';

    /**
     * Backend abstract block
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Category tree
     *
     * @var string
     */
    protected $treeElement = '.tree-holder';

    /**
     * Get backend abstract block
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            'Magento\Backend\Test\Block\Template',
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Press 'Add Subcategory' button
     *
     * @return void
     */
    public function addSubcategory()
    {
        $this->_rootElement->find($this->addSubcategory, Locator::SELECTOR_CSS)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Press 'Add Root Category' button
     *
     * @return void
     */
    public function addRootCategory()
    {
        $this->_rootElement->find($this->addRootCategory, Locator::SELECTOR_CSS)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Select Default category
     *
     * @param FixtureInterface $category
     * @param bool $fullPath
     * @return void
     */
    public function selectCategory(FixtureInterface $category, $fullPath = true)
    {
        if ($category instanceof InjectableFixture) {
            $parentPath = $this->prepareFullCategoryPath($category);
            if (!$fullPath) {
                array_pop($parentPath);
            }
            $path = implode('/', $parentPath);
        } else {
            $path = $category->getCategoryPath();
        }

        $this->expandAllCategories();
        $this->_rootElement->find($this->treeElement, Locator::SELECTOR_CSS, 'tree')->setValue($path);
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Prepare category path
     *
     * @param CatalogCategory $category
     * @return array
     */
    protected function prepareFullCategoryPath(CatalogCategory $category)
    {
        $path = [];
        $parentCategory = $category->hasData('parent_id')
            ? $category->getDataFieldConfig('parent_id')['source']->getParentCategory()
            : null;

        if ($parentCategory !== null) {
            $path = $this->prepareFullCategoryPath($parentCategory);
        }
        return array_filter(array_merge($path, [$category->getPath(), $category->getName()]));
    }

    /**
     * Find category name in array
     *
     * @param array $structure
     * @param array $category
     * @return bool
     */
    protected function inTree(array $structure, array &$category)
    {
        $element = array_shift($category);
        foreach ($structure as $item) {
            $result = strpos($item['name'], $element);
            if ($result !== false && !empty($item['subnodes'])) {
                return $this->inTree($item['subnodes'], $category);
            } elseif ($result !== false && empty($category)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check category in category tree
     *
     * @param CatalogCategory $category
     * @return bool
     */
    public function isCategoryVisible(CatalogCategory $category)
    {
        $categoryPath = $this->prepareFullCategoryPath($category);
        $structure = $this->_rootElement->find($this->treeElement, Locator::SELECTOR_CSS, 'tree')->getStructure();
        $result = false;
        $element = array_shift($categoryPath);
        foreach ($structure as $item) {
            $searchResult = strpos($item['name'], $element);
            if ($searchResult !== false && !empty($item['subnodes'])) {
                $result = $this->inTree($item['subnodes'], $categoryPath);
            } elseif ($searchResult !== false && empty($categoryPath)) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * Expand all categories tree
     *
     * @return void
     */
    protected function expandAllCategories()
    {
        $this->_rootElement->find($this->expandAll)->click();
    }
}
