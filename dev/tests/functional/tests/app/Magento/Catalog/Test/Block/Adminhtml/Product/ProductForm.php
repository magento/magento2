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

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Mtf\Client\Element;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\Product;
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Fixture\CatalogCategory;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;

/**
 * Class ProductForm
 * Product form on backend product page
 */
class ProductForm extends FormTabs
{
    /**
     * Attribute on the Product page
     *
     * @var string
     */
    protected $attribute = './/*[contains(@class,"label")]/span[text()="%s"]';

    /**
     * Attribute Search locator the Product page
     *
     * @var string
     */
    protected $attributeSearch = '#product-attribute-search-container';

    /**
     * Selector for trigger(show/hide) of advanced setting content
     *
     * @var string
     */
    protected $advancedSettingTrigger = '#product_info_tabs-advanced [data-role="trigger"]';

    /**
     * Selector for advanced setting content
     *
     * @var string
     */

    protected $advancedSettingContent = '#product_info_tabs-advanced [data-role="content"]';

    /**
     * Variations Attribute Search locator the Product page
     *
     * @var string
     */
    protected $variationsAttributeSearch = '#variations-search-field';

    /**
     * Variations Tab locator the Product page
     *
     * @var string
     */
    protected $variationsTab = '#product_info_tabs_super_config_content .title';

    /**
     * Custom Tab locator
     *
     * @var string
     */
    protected $customTab = './/*/a[contains(@id,"product_info_tabs_%s")]';

    /**
     * Fill the product form
     *
     * @param FixtureInterface $product
     * @param Element|null $element [optional]
     * @param FixtureInterface|null $category [optional]
     * @return FormTabs
     */
    public function fill(FixtureInterface $product, Element $element = null, FixtureInterface $category = null)
    {
        $tabs = $this->getFieldsByTabs($product);

        if ($category) {
            $tabs['product-details']['category_ids']['value'] = ($category instanceof InjectableFixture )
                ? $category->getName()
                : $category->getCategoryName();
        }

        $this->showAdvancedSettings();
        return parent::fillTabs($tabs, $element);
    }

    /**
     * Get data of the tabs
     *
     * @param FixtureInterface|null $fixture
     * @param Element|null $element
     * @return array
     */
    public function getData(FixtureInterface $fixture = null, Element $element = null)
    {
        $this->showAdvancedSettings();
        return parent::getData($fixture, $element);
    }

    /**
     * Show Advanced Setting
     *
     * @return void
     */
    protected function showAdvancedSettings()
    {
        if (!$this->_rootElement->find($this->advancedSettingContent)->isVisible()) {
            $this->_rootElement->find($this->advancedSettingTrigger)->click();
            $this->waitForElementVisible($this->advancedSettingContent);
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
        $this->_rootElement->find('input#new_category_name', Locator::SELECTOR_CSS)
            ->setValue($fixture->getNewCategoryName());

        $this->clearCategorySelect();
        $this->selectParentCategory();

        $this->_rootElement->find('div.ui-dialog-buttonset button.action-create')->click();
        $this->waitForElementNotVisible('div.ui-dialog-buttonset button.action-create');
    }

    /**
     * Select parent category for new one
     *
     * @return void
     */
    protected function selectParentCategory()
    {
        // TODO should be removed after suggest widget implementation as typified element
        $this->fillCategoryField(
            'Default Category',
            'new_category_parent-suggest',
            '//*[@id="new_category_form_fieldset"]'
        );
    }

    /**
     * Clear category field
     *
     * @return void
     */
    public function clearCategorySelect()
    {
        $selectedCategory = 'li.mage-suggest-choice span.mage-suggest-choice-close';
        if ($this->_rootElement->find($selectedCategory)->isVisible()) {
            $this->_rootElement->find($selectedCategory)->click();
        }
    }

    /**
     * Open new category dialog
     *
     * @return void
     */
    protected function openNewCategoryDialog()
    {
        $this->_rootElement->find('#add_category_button', Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible('input#new_category_name');
    }

    /**
     * Check visibility of the attribute on the product page
     *
     * @param mixed $productAttribute
     * @return bool
     */
    public function checkAttributeLabel($productAttribute)
    {
        $frontendLabel = (is_array($productAttribute))
            ? $productAttribute['frontend_label']
            : $productAttribute->getFrontendLabel();
        $attributeLabelLocator = sprintf($this->attribute, $frontendLabel);

        return $this->_rootElement->find($attributeLabelLocator, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Call method that checking present attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function checkAttributeInSearchAttributeForm(CatalogProductAttribute $productAttribute)
    {
        return $this->_rootElement->find(
            $this->attributeSearch,
            Locator::SELECTOR_CSS,
            'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Attributes\Search'
        )->isExistAttributeInSearchResult($productAttribute);
    }

    /**
     * Call method that checking present attribute in search result
     *
     * @param CatalogProductAttribute $productAttribute
     * @return bool
     */
    public function checkAttributeInVariationsSearchAttributeForm(CatalogProductAttribute $productAttribute)
    {
        return $this->_rootElement->find(
            $this->variationsAttributeSearch,
            Locator::SELECTOR_CSS,
            'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Search'
        )->isExistAttributeInSearchResult($productAttribute);
    }

    /**
     * Open Variations tab on Product form
     *
     * @return void
     */
    public function openVariationsTab()
    {
        if (!$this->_rootElement->find($this->variationsAttributeSearch, Locator::SELECTOR_CSS)->isVisible()) {
            $this->_rootElement->find($this->variationsTab, Locator::SELECTOR_CSS)->click();
        }
    }

    /**
     * Check tab visibility on Product form
     *
     * @param string $tabName
     * @return bool
     */
    public function isTabVisible($tabName)
    {
        $tabName = strtolower($tabName);

        return $this->_rootElement->find(sprintf($this->customTab, $tabName), Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Open custom tab on Product form
     *
     * @param string $tabName
     * @return void
     */
    public function openCustomTab($tabName)
    {
        $tabName = strtolower($tabName);
        $this->_rootElement->find(sprintf($this->customTab, $tabName), Locator::SELECTOR_XPATH)->click();
    }
}
