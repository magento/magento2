<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\CustomAttribute;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Fixture\Product;
use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\DataFixture;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Product form on backend product page.
 */
class ProductForm extends FormTabs
{
    /**
     * Attribute on the Product page.
     *
     * @var string
     */
    protected $attribute = './/*[contains(@class,"label")]/span[text()="%s"]';

    /**
     * Attribute Search locator the Product page.
     *
     * @var string
     */
    protected $attributeSearch = '#product-attribute-search-container';

    /**
     * Selector for trigger(show/hide) of advanced setting content.
     *
     * @var string
     */
    protected $advancedSettingTrigger = '#product_info_tabs-advanced [data-role="trigger"]';

    /**
     * Selector for advanced setting content.
     *
     * @var string
     */
    protected $advancedSettingContent = '#product_info_tabs-advanced [data-role="content"]';

    /**
     * Custom Tab locator.
     *
     * @var string
     */
    protected $customTab = './/*/a[contains(@id,"product_info_tabs_%s")]';

    /**
     * Button "New Category".
     *
     * @var string
     */
    protected $buttonNewCategory = '#add_category_button';

    /**
     * Dialog box "Create Category".
     *
     * @var string
     */
    protected $createCategoryDialog = './/ancestor::body//*[contains(@class,"mage-new-category-dialog")]';

    /**
     * "Parent Category" block on dialog box.
     *
     * @var string
     */
    protected $parentCategoryBlock = '//*[contains(@class,"field-new_category_parent")]';

    /**
     * Field "Category Name" on dialog box.
     *
     * @var string
     */
    protected $fieldNewCategoryName = '//input[@id="new_category_name"]';

    /**
     * Button "Create Category" on dialog box.
     *
     * @var string
     */
    protected $createCategoryButton = '//button[contains(@class,"action-create")]';

    /**
     * Tabs title css selector.
     *
     * @var string
     */
    protected $tabsTitle = '#product_info_tabs-basic [data-role="title"]';

    /**
     * Attribute block selector.
     *
     * @var string
     */
    protected $attributeBlock = '#attribute-%s-container';

    /**
     * Magento loader.
     *
     * @var string
     */
    protected $loader = '[data-role="loader"]';

    /**
     * New attribute form selector.
     *
     * @var string
     */
    protected $newAttributeForm = '#create_new_attribute';

    /**
     * Fill the product form.
     *
     * @param FixtureInterface $product
     * @param Element|null $element [optional]
     * @param FixtureInterface|null $category [optional]
     * @return FormTabs
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fill(FixtureInterface $product, Element $element = null, FixtureInterface $category = null)
    {
        $dataConfig = $product->getDataConfig();
        $typeId = isset($dataConfig['type_id']) ? $dataConfig['type_id'] : null;

        if ($this->hasRender($typeId)) {
            $renderArguments = [
                'product' => $product,
                'element' => $element,
                'category' => $category,
            ];
            $this->callRender($typeId, 'fill', $renderArguments);
        } else {
            $tabs = $this->getFieldsByTabs($product);

            if (null === $category && $product instanceof DataFixture) {
                $categories = $product->getCategories();
                $category = reset($categories);
            }
            if ($category) {
                $tabs['product-details']['category_ids']['value'] = ($category instanceof InjectableFixture)
                    ? $category->getName()
                    : $category->getCategoryName();
            }

            $this->showAdvancedSettings();
            $this->fillTabs($tabs, $element);

            if ($product instanceof InjectableFixture && $product->hasData('custom_attribute')) {
                $this->createCustomAttribute($product);
            }
        }

        return $this;
    }

    /**
     * Create custom attribute.
     *
     * @param InjectableFixture $product
     * @param string $tabName
     * @return void
     */
    protected function createCustomAttribute(InjectableFixture $product, $tabName = 'product-details')
    {
        $attribute = $product->getDataFieldConfig('custom_attribute')['source']->getAttribute();
        $this->openTab('product-details');
        if (!$this->checkAttributeLabel($attribute)) {
            /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab $tab */
            $tab = $this->openTab($tabName);
            $tab->addNewAttribute($tabName);
            $this->fillAttributeForm($attribute);
            $this->reinitRootElement();
        }
    }

    /**
     * Get data of the tabs.
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
     * Show Advanced Setting.
     *
     * @return void
     */
    protected function showAdvancedSettings()
    {
        if (!$this->_rootElement->find($this->advancedSettingContent)->isVisible()) {
            $this->_rootElement->find($this->advancedSettingTrigger)->click();
            $this->waitForElementVisible($this->advancedSettingContent);
        }
        $this->_rootElement->find($this->tabsTitle)->click();
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        $this->showAdvancedSettings();
        return parent::openTab($tabName);
    }

    /**
     * Save new category.
     *
     * @param Product $fixture
     * @return void
     */
    public function addNewCategory(Product $fixture)
    {
        $this->openTab('product-details');
        $this->openNewCategoryDialog();
        $this->_rootElement->find(
            $this->createCategoryDialog . $this->fieldNewCategoryName,
            Locator::SELECTOR_XPATH
        )->setValue($fixture->getNewCategoryName());

        $this->clearCategorySelect();
        $this->selectParentCategory();

        $buttonCreateCategory = $this->createCategoryDialog . $this->createCategoryButton;
        $this->_rootElement->find($buttonCreateCategory, Locator::SELECTOR_XPATH)->click();
        $this->waitForElementNotVisible($buttonCreateCategory, Locator::SELECTOR_XPATH);
    }

    /**
     * Select parent category for new one.
     *
     * @return void
     */
    protected function selectParentCategory()
    {
        $this->_rootElement->find(
            $this->createCategoryDialog . $this->parentCategoryBlock,
            Locator::SELECTOR_XPATH,
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails\ParentCategoryIds'
        )->setValue('Default Category');
    }

    /**
     * Clear category field.
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
     * Open new category dialog.
     *
     * @return void
     */
    protected function openNewCategoryDialog()
    {
        $this->_rootElement->find($this->buttonNewCategory)->click();
        $this->waitForElementVisible($this->createCategoryDialog, Locator::SELECTOR_XPATH);
    }

    /**
     * Check visibility of the attribute on the product page.
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
     * Call method that checking present attribute in search result.
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
     * Check tab visibility on Product form.
     *
     * @param string $tabName
     * @return bool
     */
    public function isTabVisible($tabName)
    {
        $tabName = strtolower($tabName);
        $selector = sprintf($this->customTab, $tabName);
        $this->waitForElementVisible($selector, Locator::SELECTOR_XPATH);
        return $this->_rootElement->find($selector, Locator::SELECTOR_XPATH)->isVisible();
    }

    /**
     * Open custom tab on Product form.
     *
     * @param string $tabName
     * @return void
     */
    public function openCustomTab($tabName)
    {
        $tabName = strtolower($tabName);
        $this->_rootElement->find(sprintf($this->customTab, $tabName), Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get Require Notice Attributes.
     *
     * @param InjectableFixture $product
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getRequireNoticeAttributes(InjectableFixture $product)
    {
        $data = [];
        $tabs = $this->getFieldsByTabs($product);
        foreach ($tabs as $tabName => $fields) {
            $tab = $this->getTabElement($tabName);
            $this->openTab($tabName);
            $errors = $tab->getJsErrors();
            if (!empty($errors)) {
                $data[$tabName] = $errors;
            }
        }
        return $data;
    }

    /**
     * Fill product attribute form.
     *
     * @param CatalogProductAttribute $productAttribute
     * @return void
     */
    public function fillAttributeForm(CatalogProductAttribute $productAttribute)
    {
        $attributeForm = $this->getAttributeForm();
        $attributeForm->fill($productAttribute);
    }

    /**
     * Click "Save" button on attribute form.
     *
     * @return void
     */
    public function saveAttributeForm()
    {
        $this->getAttributeForm()->saveAttributeForm();

        $browser = $this->browser;
        $element = $this->newAttributeForm;
        $loader = $this->loader;
        $this->_rootElement->waitUntil(
            function () use ($browser, $element) {
                return $browser->find($element)->isVisible() == false ? true : null;
            }
        );

        $this->_rootElement->waitUntil(
            function () use ($browser, $loader) {
                return $browser->find($loader)->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Get Attribute Form.
     *
     * @return AttributeForm
     */
    public function getAttributeForm()
    {
        /** @var AttributeForm $attributeForm */
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm',
            ['element' => $this->browser->find('body')]
        );
    }

    /**
     * Get attribute element.
     *
     * @param CatalogProductAttribute $attribute
     * @return CustomAttribute
     */
    public function getAttributeElement(CatalogProductAttribute $attribute)
    {
        return $this->_rootElement->find(
            sprintf($this->attributeBlock, $attribute->getAttributeCode()),
            Locator::SELECTOR_CSS,
            'Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\CustomAttribute'
        );
    }

    /**
     * Click "Add Attribute" button from specific tab.
     *
     * @param string $tabName
     * @throws \Exception
     */
    public function addNewAttribute($tabName = 'product-details')
    {
        $tab = $this->getTabElement($tabName);
        if ($tab instanceof ProductTab) {
            $this->openTab($tabName);
            $tab->addNewAttribute($tabName);
        } else {
            throw new \Exception("$tabName hasn't 'Add attribute' button or is not instance of ProductTab class.");
        }
    }
}
