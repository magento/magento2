<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category;

use Magento\Backend\Test\Block\Template;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Categories tree block.
 */
class Tree extends Block
{
    /**
     * 'Add Subcategory' button.
     *
     * @var string
     */
    protected $addSubcategory = '#add_subcategory_button';

    /**
     * 'Add Root Category' button.
     *
     * @var string
     */
    protected $addRootCategory = '#add_root_category_button';

    /**
     * 'Expand All' link.
     *
     * @var string
     */
    protected $expandAll = 'a[onclick*=expandTree]';

    /**
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * Category tree.
     *
     * @var string
     */
    protected $treeElement = '.tree-holder';

    /**
     * Page header selector.
     *
     * @var string
     */
    protected $header = 'header';

    /**
     * Xpath locator for category in tree.
     *
     * @var string
     */
    private $categoryInTree = '//ul//li//span[contains(text(), "%s")]';

    /**
     * Get backend abstract block.
     *
     * @return Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Press 'Add Subcategory' button.
     *
     * @return void
     */
    public function addSubcategory()
    {
        $this->browser->find($this->header)->hover();
        $this->_rootElement->find($this->addSubcategory, Locator::SELECTOR_CSS)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Press 'Add Root Category' button.
     *
     * @return void
     */
    public function addRootCategory()
    {
        $this->browser->find($this->header)->hover();
        $this->_rootElement->find($this->addRootCategory, Locator::SELECTOR_CSS)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Select Default category.
     *
     * @param FixtureInterface $category
     * @param bool $fullPath
     * @return void
     */
    public function selectCategory(FixtureInterface $category, $fullPath = true)
    {
        $parentPath = $this->prepareFullCategoryPath($category);
        if (!$fullPath) {
            array_pop($parentPath);
        }
        if (empty($parentPath)) {
            return;
        }
        $path = implode('/', $parentPath);

        $this->expandAllCategories();
        $this->_rootElement->find($this->treeElement, Locator::SELECTOR_CSS, 'tree')->setValue($path);
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Prepare category path.
     *
     * @param Category $category
     * @return array
     */
    protected function prepareFullCategoryPath(Category $category)
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
     * Check category in category tree.
     *
     * @param Category $category
     * @return bool
     */
    public function isCategoryVisible(Category $category)
    {
        $categoryPath = $this->prepareFullCategoryPath($category);
        $categoryPath = implode('/', $categoryPath);
        return $this->_rootElement->find($this->treeElement, Locator::SELECTOR_CSS, 'tree')
            ->isElementVisible($categoryPath);
    }

    /**
     * Assign child category to the parent.
     *
     * @param string $parentCategoryName
     * @param string $childCategoryName
     *
     * @return void
     */
    public function assignCategory($parentCategoryName, $childCategoryName)
    {
        $this->_rootElement->find(sprintf($this->categoryInTree, $childCategoryName), Locator::SELECTOR_XPATH)->click();
        $this->getTemplateBlock()->waitLoader();
        $targetElement = $this->_rootElement->find(
            sprintf($this->categoryInTree, $parentCategoryName),
            Locator::SELECTOR_XPATH
        );
        $targetElement->hover();
        $this->_rootElement->find(sprintf($this->categoryInTree, $childCategoryName), Locator::SELECTOR_XPATH)
            ->dragAndDrop($targetElement);
    }

    /**
     * Expand all categories tree.
     *
     * @return void
     */
    public function expandAllCategories()
    {
        $this->_rootElement->find($this->expandAll)->click();
    }
}
