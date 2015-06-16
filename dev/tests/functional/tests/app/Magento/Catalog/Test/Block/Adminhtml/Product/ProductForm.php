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
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\ProductTab;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Attributes\Search;

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
     * @param SimpleElement|null $element [optional]
     * @param FixtureInterface|null $category [optional]
     * @return FormTabs
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fill(FixtureInterface $product, SimpleElement $element = null, FixtureInterface $category = null)
    {
        $this->waitPageToLoad();
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

            if ($category) {
                $tabs['product-details']['category_ids']['value'] = $category->getName();
            }
            $this->fillTabs($tabs, $element);

            if ($product->hasData('custom_attribute')) {
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
            /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\ProductDetails $tab */
            $tab = $this->openTab($tabName);
            $tab->addNewAttribute($tabName);
            $this->getAttributeForm()->fill($attribute);
        }
    }

    /**
     * Get data of the tabs.
     *
     * @param FixtureInterface|null $fixture
     * @param SimpleElement|null $element
     * @return array
     */
    public function getData(FixtureInterface $fixture = null, SimpleElement $element = null)
    {
        $this->showAdvancedSettings();

        return parent::getData($fixture, $element);
    }

    /**
     * Open tab.
     *
     * @param string $tabName
     * @return Tab
     */
    public function openTab($tabName)
    {
        if (!$this->isTabVisible($tabName)) {
            $this->showAdvancedSettings();
        }
        return parent::openTab($tabName);
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
     * Wait page to load.
     *
     * @return void
     */
    protected function waitPageToLoad()
    {
        $browser = $this->browser;
        $element = $this->advancedSettingContent;
        $advancedSettingTrigger = $this->advancedSettingTrigger;

        $this->_rootElement->waitUntil(
            function () use ($browser, $advancedSettingTrigger) {
                return $browser->find($advancedSettingTrigger)->isVisible() == true ? true : null;
            }
        );

        $this->_rootElement->waitUntil(
            function () use ($browser, $element) {
                return $browser->find($element)->isVisible() == false ? true : null;
            }
        );
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
        $this->waitPageToLoad();
        return $this->getAttributesSearchForm()->isExistAttributeInSearchResult($productAttribute);
    }

    /**
     * Get attributes search form.
     *
     * @return Search
     */
    protected function getAttributesSearchForm()
    {
        return $this->_rootElement->find(
            $this->attributeSearch,
            Locator::SELECTOR_CSS,
            'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Attributes\Search'
        );
    }

    /**
     * Check custom tab visibility on Product form.
     *
     * @param string $tabName
     * @return bool
     */
    public function isCustomTabVisible($tabName)
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
            $tab = $this->getTab($tabName);
            $this->openTab($tabName);
            $errors = $tab->getJsErrors();
            if (!empty($errors)) {
                $data[$tabName] = $errors;
            }
        }

        return $data;
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
        $tab = $this->getTab($tabName);
        if ($tab instanceof ProductTab) {
            $this->openTab($tabName);
            $tab->addNewAttribute($tabName);
        } else {
            throw new \Exception("$tabName hasn't 'Add attribute' button or is not instance of ProductTab class.");
        }
    }
}
