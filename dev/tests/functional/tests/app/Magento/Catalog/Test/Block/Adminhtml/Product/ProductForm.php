<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\CustomAttribute;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails\NewCategoryIds;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Ui\Test\Block\Adminhtml\FormSections;

/**
 * Product form on backend product page.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductForm extends FormSections
{
    /**
     * Attribute on the Product page.
     *
     * @var string
     */
    protected $attribute = './/*[contains(@class,"label")]/label[text()="%s"]';

    /**
     * Product new from date field on the product form
     *
     * @var string
     */
    protected $news_from_date ='[name="product[news_from_date]"]';

    /**
     * Attributes Search modal locator.
     *
     * @var string
     */
    protected $attributeSearch = '.product_form_product_form_add_attribute_modal';

    /**
     * Custom Section locator.
     *
     * @var string
     */
    protected $customSection = '[data-index="%s"] .admin__collapsible-title';

    /**
     * Attribute block selector.
     *
     * @var string
     */
    protected $attributeBlock = '[data-index="%s"]';

    /**
     * NewCategoryIds block selector.
     *
     * @var string
     */
    protected $newCategoryModalForm = '.product_form_product_form_create_category_modal';

    /**
     * Magento form loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * New Attribute modal locator.
     *
     * @var string
     */
    protected $newAttributeModal = '.product_form_product_form_add_attribute_modal_create_new_attribute_modal';

    /**
     * Website checkbox xpath selector.
     *
     * @var string
     */
    protected $websiteCheckbox = '//label[text()="%s"]/../input';

    /**
     * Fill the product form.
     *
     * @param FixtureInterface $product
     * @param SimpleElement|null $element
     * @param FixtureInterface|null $category
     * @return $this
     * @throws \Exception
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
            if ($product->hasData('category_ids') || $category) {
                $sections['product-details']['category_ids']['value'] = [];
                $categories = $product->hasData('category_ids')
                    ? $product->getDataFieldConfig('category_ids')['source']->getCategories()
                    : [$category];
                foreach ($categories as $category) {
                    if ((int)$category->getId()) {
                        $sections['product-details']['category_ids']['value'][] = $category->getName();
                    } else {
                        $this->getNewCategoryModalForm()->addNewCategory($category);
                    }
                }
                if (empty($sections['product-details']['category_ids']['value'])) {
                    // We need to clear 'category_ids' key in case of category(es) absence in Product Fixture
                    // to avoid force clear related form input on edit product page
                    unset($sections['product-details']['category_ids']);
                }
            }
            $this->fillContainers($sections, $element);
        }

        return $this;
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
            sleep(2); // according to animation timeout in JS
        } else {
            parent::openSection($sectionName);
        }
        return $this;
    }

    /**
     * Unassign product from website by website name.
     *
     * @param string $name
     */
    public function unassignFromWebsite($name)
    {
        $this->openSection('websites');
        $this->_rootElement->find(sprintf($this->websiteCheckbox, $name), Locator::SELECTOR_XPATH)->click();
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
     * Get attributes search grid.
     *
     * @return DataGrid
     */
    public function getAttributesSearchGrid()
    {
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Attributes\Grid::class,
            ['element' => $this->browser->find($this->attributeSearch)]
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
        $selector = sprintf($this->attributeBlock, $sectionName);

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
     * Get Attribute Form.
     *
     * @return AttributeForm
     */
    public function getAttributeForm()
    {
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm::class,
            ['element' => $this->browser->find($this->newAttributeModal)]
        );
    }

    /**
     * Get New Category Modal Form.
     *
     * @return NewCategoryIds
     */
    public function getNewCategoryModalForm()
    {
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\ProductDetails\NewCategoryIds::class,
            ['element' => $this->browser->find($this->newCategoryModalForm)]
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
            \Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\CustomAttribute::class
        );
    }

    /**
     * @param $sectionName
     * @return bool
     */
    public function isProductNewFromDateVisible($sectionName)
    {
        $this->openSection($sectionName);
        return $this->_rootElement->find($this->news_from_date, Locator::SELECTOR_CSS)->isVisible();
    }
}
