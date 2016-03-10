<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\CustomAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Attributes;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Catalog\Test\Fixture\Category;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Attributes\Search;

/**
 * Product form on backend product page.
 */
class ProductForm extends FormSections
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
     * Custom Section locator.
     *
     * @var string
     */
    protected $customSection = '.admin__collapsible-title';

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
     * Magento form loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * Fill the product form.
     *
     * @param FixtureInterface $product
     * @param SimpleElement|null $element
     * @param FixtureInterface|null $category
     * @return $this
     * @throws \Exception
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
            $sections = $this->getFixtureFieldsByContainers($product);

            if ($category) {
                $sections['product-details']['category_ids']['value'] = $category->getName();
            }
            $this->fillContainers($sections, $element);

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
        $this->openSection('product-details');
        if (!$this->checkAttributeLabel($attribute)) {
            /** @var \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails $section */
            $section = $this->openSection($tabName);
            $section->addNewAttribute($tabName);
            $this->getAttributeForm()->fill($attribute);
        }
    }

    /**
     * Open section or click on button to open modal window.
     *
     * @param string $sectionName
     * @return $this
     */
    public function openSection($sectionName)
    {
        $sectionElement = $this->getContainerElement($sectionName);
        if ($sectionElement->getAttribute('type') == 'button') {
            $sectionElement->click();
        } else {
            parent::openSection($sectionName);
        }
        return $this;
    }

    /**
     * Wait page to load.
     *
     * @return void
     */
    protected function waitPageToLoad()
    {
        $this->waitForElementNotVisible($this->spinner);
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
            'Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Attributes\Search'
        );
    }

    /**
     * Check custom section visibility on Product form.
     *
     * @param string $sectionName
     * @return bool
     */
    public function isCustomSectionVisible($sectionName)
    {
        $sectionName = strtolower($sectionName);
        $selector = sprintf($this->customSection, $sectionName);
        $this->waitForElementVisible($selector);

        return $this->_rootElement->find($selector)->isVisible();
    }

    /**
     * Open custom section on Product form.
     *
     * @param string $sectionName
     * @return void
     */
    public function openCustomSection($sectionName)
    {
        $sectionName = strtolower($sectionName);
        $this->_rootElement->find(sprintf($this->customSection, $sectionName))->click();
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
     * Click "Add Attribute" button from specific section.
     *
     * @param string $sectionName
     * @throws \Exception
     */
    public function addNewAttribute($sectionName = 'product-details')
    {
        $section = $this->getSection($sectionName);
        if ($section instanceof Attributes) {
            $this->openSection($sectionName);
            $section->addNewAttribute($sectionName);
        } else {
            throw new \Exception(
                "$sectionName hasn't 'Add attribute' button or is not instance of ProductSection class."
            );
        }
    }
}
