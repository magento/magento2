<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product;

use Magento\Ui\Test\Block\Adminhtml\FormSections;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm;
use Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\CustomAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Ui\Test\Block\Adminhtml\DataGrid;

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
            // Wait until section animation finished.
            $this->waitForElementVisible($this->closeButton);
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
}
