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

namespace Magento\Catalog\Test\Block\Backend;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\Product;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Catalog\Test\Fixture\ConfigurableProduct;

/**
 * Class ProductForm
 * Product creation form
 */
class ProductForm extends FormTabs
{
    /**
     * 'Save' split button
     *
     * @var string
     */
    protected $saveButton = '#save-split-button-button';

    /**
     * Variations tab selector
     *
     * @var string
     */
    protected $variationsTab = '[data-ui-id="product-tabs-tab-content-super-config"] .title';

    /**
     * Variations wrapper selector
     *
     * @var string
     */
    protected $variationsWrapper = '[data-ui-id="product-tabs-tab-content-super-config"]';

    /**
     * New variation set button selector
     *
     * @var string
     */
    protected $newVariationSet = '[data-ui-id="admin-product-edit-tab-super-config-grid-container-add-attribute"]';

    /**
     * Choose affected attribute set dialog popup window
     *
     * @var string
     */
    protected $affectedAttributeSet = "//div[div/@data-id='affected-attribute-set-selector']";

    /**
     * Category name selector
     *
     * @var string
     */
    protected $categoryName = '//*[contains(@class, "mage-suggest-choice")]/*[text()="%categoryName%"]';

    /**
     * 'Advanced Settings' tab
     *
     * @var string
     */
    protected $advancedSettings = '#product_info_tabs-advanced [data-role="trigger"]';

    /**
     * Advanced tab list
     *
     * @var string
     */
    protected $advancedTabList = '#product_info_tabs-advanced[role="tablist"]';

    /**
     * Advanced tab panel
     *
     * @var string
     */
    protected $advancedTabPanel = '[role="tablist"] [role="tabpanel"][aria-expanded="true"]:not("overflow")';

    /**
     * Selector popups 'New category'
     *
     * @var string
     */
    protected $newCategoryPopup = "./ancestor::body//div[div[contains(@id,'new-category')]]";

    /**
     * Category fixture
     *
     * @var Category
     */
    protected $category;

    /**
     * Get choose affected attribute set dialog popup window
     *
     * @return \Magento\Catalog\Test\Block\Product\Configurable\AffectedAttributeSet
     */
    protected function getAffectedAttributeSetBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogProductConfigurableAffectedAttributeSet(
            $this->_rootElement->find($this->affectedAttributeSet, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Set category
     *
     * @param Category $category
     * @return void
     */
    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Fill the product form
     *
     * @param FixtureInterface $fixture
     * @param Element $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        $this->fillCategory($fixture);
        return parent::fill($fixture);
    }

    /**
     * Select category
     *
     * @param FixtureInterface $fixture
     * @return void
     */
    protected function fillCategory(FixtureInterface $fixture)
    {
        // TODO should be removed after suggest widget implementation as typified element
        $categoryName = $this->category
            ? $this->category->getCategoryName()
            : ($fixture->getCategoryName() ? $fixture->getCategoryName() : '');

        if (!$categoryName) {
            return;
        }
        $category = $this->_rootElement->find(
            str_replace('%categoryName%', $categoryName, $this->categoryName),
            Locator::SELECTOR_XPATH
        );
        if (!$category->isVisible()) {
            $this->fillCategoryField(
                $categoryName,
                'category_ids-suggest',
                '//*[@id="attribute-category_ids-container"]'
            );
        }
    }

    /**
     * Save product
     *
     * @param FixtureInterface $fixture
     * @return \Magento\Backend\Test\Block\Widget\Form|void
     */
    public function save(FixtureInterface $fixture = null)
    {
        parent::save($fixture);
        if ($this->getAffectedAttributeSetBlock()->isVisible()) {
            $this->getAffectedAttributeSetBlock()->chooseAttributeSet($fixture);
        }
    }

    /**
     * Save new category
     *
     * @param Product $fixture
     * @return void
     */
    public function addNewCategory(Product $fixture)
    {
        $this->openNewCategoryDialog();
        $popupElement =  $this->_rootElement->find($this->newCategoryPopup, Locator::SELECTOR_XPATH);

        if ($popupElement->isVisible()) {
            $popupElement->find('input#new_category_name', Locator::SELECTOR_CSS)
                ->setValue($fixture->getNewCategoryName());
            $this->clearCategorySelect($popupElement);
            $this->selectParentCategory($popupElement);
            $popupElement->find('div.ui-dialog-buttonset button.action-create')->click();
            $this->waitForElementNotVisible('div.ui-dialog-buttonset button.action-create');
        }

    }

    /**
     * Get variations block
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config
     */
    protected function getVariationsBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabSuperConfig(
            $this->_rootElement->find($this->variationsWrapper)
        );
    }

    /**
     * Fill product variations
     *
     * @param ConfigurableProduct $variations
     * @return void
     */
    public function fillVariations(ConfigurableProduct $variations)
    {
        $variationsBlock = $this->getVariationsBlock();
        $variationsBlock->fillAttributeOptions($variations->getConfigurableAttributes());
        $variationsBlock->generateVariations();
        $variationsBlock->fillVariationsMatrix($variations->getVariationsMatrix());
    }

    /**
     * Open variations tab
     *
     * @return void
     */
    public function openVariationsTab()
    {
        $this->_rootElement->find($this->variationsTab)->click();
    }

    /**
     * Click on 'Create New Variation Set' button
     *
     * @return void
     */
    public function clickCreateNewVariationSet()
    {
        $this->_rootElement->find($this->newVariationSet)->click();
    }

    /**
     * Clear category field
     *
     * @param Element $element
     * @return void
     */
    public function clearCategorySelect(Element $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $selectedCategory = 'li.mage-suggest-choice span.mage-suggest-choice-close';
        if ($element->find($selectedCategory)->isVisible()) {
            $element->find($selectedCategory)->click();
        }
    }

    /**
     * Select parent category for new one
     *
     * @param Element $element
     * @return void
     */
    protected function selectParentCategory(Element $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        // TODO should be removed after suggest widget implementation as typified element
        $this->fillCategoryField(
            'Default Category',
            'new_category_parent-suggest',
            '//*[@id="new_category_form_fieldset"]',
            $element
        );
    }

    /**
     * Fills select category field
     *
     * @param string $name
     * @param string $elementId
     * @param string $parentLocation
     * @param Element $element
     * @return void
     */
    protected function fillCategoryField($name, $elementId, $parentLocation, Element $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        // TODO should be removed after suggest widget implementation as typified element
        $element->find($elementId, Locator::SELECTOR_ID)->setValue($name);
        //*[@id="attribute-category_ids-container"]  //*[@id="new_category_form_fieldset"]
        $categoryListLocation = $parentLocation . '//div[@class="mage-suggest-dropdown"]'; //
        $this->waitForElementVisible($categoryListLocation, Locator::SELECTOR_XPATH);
        $categoryLocation = $parentLocation . '//li[contains(@data-suggest-option, \'"label":"' . $name . '",\')]//a';
        $element->find($categoryLocation, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Open new category dialog
     *
     * @return void
     */
    protected function openNewCategoryDialog()
    {
        $this->_rootElement->find('#add_category_button', Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->newCategoryPopup, Locator::SELECTOR_XPATH);
    }

    /**
     * Open tab
     *
     * @param string $tabName
     * @return Tab|bool
     */
    public function openTab($tabName)
    {
        $rootElement = $this->_rootElement;
        $selector = $this->tabs[$tabName]['selector'];
        $strategy = isset($this->tabs[$tabName]['strategy'])
            ? $this->tabs[$tabName]['strategy']
            : Locator::SELECTOR_CSS;
        $advancedTabList = $this->advancedTabList;
        $tab = $this->_rootElement->find($selector, $strategy);
        $advancedSettings = $this->_rootElement->find($this->advancedSettings);

        // Wait until all tabs will load
        $this->_rootElement->waitUntil(
            function () use ($rootElement, $advancedTabList) {
                return $rootElement->find($advancedTabList)->isVisible();
            }
        );

        if ($tab->isVisible()) {
            $tab->click();
        } elseif ($advancedSettings->isVisible()) {
            $advancedSettings->click();
            // Wait for open tab animation
            $tabPanel = $this->advancedTabPanel;
            $this->_rootElement->waitUntil(
                function () use ($rootElement, $tabPanel) {
                    return $rootElement->find($tabPanel)->isVisible();
                }
            );
            // Wait until needed tab will appear
            $this->_rootElement->waitUntil(
                function () use ($rootElement, $selector, $strategy) {
                    return $rootElement->find($selector, $strategy)->isVisible();
                }
            );
            $tab->click();
        } else {
            return false;
        }

        return $this;
    }
}
