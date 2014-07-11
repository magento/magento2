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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category;

use Magento\Catalog\Test\Fixture\CatalogCategory;
use Mtf\Block\Block;
use Mtf\Factory\Factory;
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
    protected $addSubcategory = 'add_subcategory_button';

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
        return Factory::getBlockFactory()->getMagentoBackendTemplate(
            $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Press 'Add Subcategory' button
     *
     * @return void
     */
    public function addSubcategory()
    {
        $this->_rootElement->find($this->addSubcategory, Locator::SELECTOR_ID)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Select Default category
     *
     * @param FixtureInterface $category
     * @return void
     */
    public function selectCategory(FixtureInterface $category)
    {
        if ($category instanceof InjectableFixture) {
            $parentPath = $this->prepareFullCategoryPath($category);
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
        if ($category->getDataFieldConfig('parent_id')['source']->getParentCategory() != null) {
            $path = $this->prepareFullCategoryPath(
                $category->getDataFieldConfig('parent_id')['source']->getParentCategory()
            );
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
     * @param $category
     * @return bool
     */
    public function isCategoryVisible($category)
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
