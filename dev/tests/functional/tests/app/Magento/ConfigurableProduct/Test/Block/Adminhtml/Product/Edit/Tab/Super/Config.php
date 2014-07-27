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

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super;

use Mtf\Client\Element;
use Mtf\Factory\Factory;
use Mtf\Client\Element\Locator;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class Variations
 * Adminhtml catalog super product configurable tab
 */
class Config extends Tab
{
    /**
     * 'Generate Variations' button
     *
     * @var string
     */
    protected $generateVariations = '[data-ui-id=product-variations-generator-generate]';

    /**
     * Product variations matrix block
     *
     * @var string
     */
    protected $matrixBlock = '[data-role=product-variations-matrix] table';

    /**
     * Product attribute block selector by attribute name
     *
     * @var string
     */
    protected $attribute = '//div[*/*/span[contains(text(), "%s")]]';

    /**
     * Magento loader
     *
     * @var string
     */
    protected $loader = './ancestor::body//*[contains(@data-role,"loader")]';

    /**
     * Attribute Opened
     *
     * @var string
     */
    protected $attributeOpen = './/*[@class = "title active"]/span[text()="%attributeLabel%"]';

    /**
     * Attribute tab
     *
     * @var string
     */
    protected $attributeTab = './/*[@data-role="configurable-attribute"]//*[text()="%attributeTab%"]';

    /**
     * XPath Selector attribute variations row content
     *
     * @var string
     */
    protected $activeButtonSelector = '//*[contains(@class,"fieldset-wrapper-title")]//*[@class="title"]';

    /**
     * XPath Selector attribute variations row
     *
     * @var string
     */
    protected $rowSelector = '//div[contains(@data-role,"configurable-attribute") and position()=%d]';

    /**
     * XPath Selector attribute options row
     *
     * @var string
     */
    protected $rowOptions = './/tbody/tr[contains(@data-role,"option-container") and position()=%d]';

    /**
     * XPath Selector attribute options row
     *
     * @var string
     */
    protected $rowMatrix = './/tbody/tr[contains(@data-role,"row") and position()=%d]';

    /**
     * CSS selector variations label
     *
     * @var string
     */
    protected $labelSelector = '.store-label';

    /**
     * Get attribute block
     *
     * @param string $attributeName
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Attribute
     */
    public function getAttributeBlock($attributeName)
    {
        $attributeSelector = sprintf($this->attribute, $attributeName);
        $this->waitForElementVisible($attributeSelector, Locator::SELECTOR_XPATH);
        return Factory::getBlockFactory()->getMagentoConfigurableProductAdminhtmlProductEditTabSuperAttribute(
            $this->_rootElement->find($attributeSelector, Locator::SELECTOR_XPATH)
        );
    }

    /**
     * Get product variations matrix block
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Matrix
     */
    protected function getMatrixBlock()
    {
        return Factory::getBlockFactory()->getMagentoCatalogAdminhtmlProductEditTabSuperConfigMatrix(
            $this->_rootElement->find($this->matrixBlock, Locator::SELECTOR_CSS)
        );
    }

    /**
     * Press 'Generate Variations' button
     *
     * @return void
     */
    public function generateVariations()
    {
        $this->_rootElement->find($this->generateVariations, Locator::SELECTOR_CSS)->click();
        $this->waitForElementVisible($this->matrixBlock, Locator::SELECTOR_CSS);
    }

    /**
     * Fill variations fieldset
     *
     * @param array $fields
     * @param Element|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, Element $element = null)
    {
        if (!isset($fields['configurable_attributes_data'])) {
            return $this;
        }
        $attributes = $fields['configurable_attributes_data']['value'];
        foreach ($attributes['attributes_data'] as $attribute) {
            $this->selectAttribute($attribute['title']);
        }
        $this->fillAttributeOptions($attributes);
        $this->generateVariations();
        if (!empty($attributes['matrix'])) {
            $this->fillVariationsMatrix($attributes['matrix']);
        }

        return $this;
    }

    /**
     * Fill variations matrix
     *
     * @param array $fields
     * @return void
     */
    public function fillVariationsMatrix(array $fields)
    {
        $this->getMatrixBlock()->fillVariation($fields);
    }

    /**
     * Fill attribute options
     *
     * @param array $attributes
     * @return void
     */
    public function fillAttributeOptions(array $attributes)
    {
        foreach ($attributes['attributes_data'] as $attribute) {
            $this->getAttributeBlock($attribute['title'])->fillAttributeOptions($attribute);
        }
    }

    /**
     * Select attribute for variations
     *
     * @param string $attributeName
     * @return void
     */
    private function selectAttribute($attributeName)
    {
        // TODO should be removed after suggest widget implementation as typified element
        $attributeFieldSet = $this->_rootElement
            ->find(str_replace('%attributeLabel%', $attributeName, $this->attributeOpen), Locator::SELECTOR_XPATH);
        $attributeVisible = $this->_rootElement
            ->find(str_replace('%attributeTab%', $attributeName, $this->attributeTab), Locator::SELECTOR_XPATH);
        if ($attributeVisible->isVisible()) {
            if (!$attributeFieldSet->isVisible()) {
                $attributeVisible->click();
            }
        } else {
            $this->_rootElement->find('#configurable-attribute-selector')->setValue($attributeName);
            $attributeListLocation = '#variations-search-field .mage-suggest-dropdown';
            $this->waitForElementVisible($attributeListLocation, Locator::SELECTOR_CSS);
            $attribute = $this->_rootElement->find(
                "//div[@class='mage-suggest-dropdown']//a[text()='$attributeName']",
                Locator::SELECTOR_XPATH
            );
            $attribute->waitUntil(
                function () use ($attribute) {
                    return $attribute->isVisible() ? true : null;
                }
            );
            $attribute->click();
        }
    }

    /**
     * Get data of tab
     *
     * @param array|null $fields [optional]
     * @param Element|null $element [optional]
     * @return array
     */
    public function getDataFormTab($fields = null, Element $element = null)
    {
        $dataResult = [];
        if (isset($fields['configurable_attributes_data']['value']['attributes_data'])) {
            $field['attributes_data']['value'] = $fields['configurable_attributes_data']['value']['attributes_data'];
            $data = $this->dataMapping($field)['attributes_data'];
            $variationsBlock = $this->_rootElement->find($data['selector']);

            foreach ($data['value'] as $key => $value) {
                ++$key;
                $this->openVariation($key, $variationsBlock);
                $row = $variationsBlock->find(sprintf($this->rowSelector, $key), Locator::SELECTOR_XPATH);
                --$key;
                $dataResult['attributes_data'][$key]['title'] = $row->find($this->labelSelector)->getValue();
                $dataResult['attributes_data'][$key]['options'] = [];
                foreach ($value['options'] as $optionKey => $option) {
                    ++$optionKey;
                    $optionsForm = $this->blockFactory->create(
                        'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Options',
                        ['element' => $row->find(sprintf($this->rowOptions, $optionKey), Locator::SELECTOR_XPATH)]
                    );
                    $dataResult['attributes_data'][$key]['options'][] = $optionsForm->getDataOptions($option);
                }
            }
        }
        if (isset($fields['configurable_attributes_data']['value']['matrix'])) {
            $field['matrix']['value'] = $fields['configurable_attributes_data']['value']['matrix'];
            $data = $this->dataMapping($field)['matrix'];
            $matrixBlock = $this->_rootElement->find($data['selector']);

            $index = 1;
            foreach ($data['value'] as $key => $value) {
                $matrixCell = $this->blockFactory->create(
                    'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Matrix',
                    ['element' => $matrixBlock->find(sprintf($this->rowMatrix, $index), Locator::SELECTOR_XPATH)]
                );
                $dataResult['matrix'][$key] = $matrixCell->getDataOptions();
                ++$index;
            }
        }

        return ['configurable_attributes_data' => $dataResult];
    }

    /**
     * Active variation tab
     *
     * @param int $row
     * @param Element $variationsBlock
     * @return void
     */
    protected function openVariation($row, Element $variationsBlock)
    {
        $element = $variationsBlock->find(
            sprintf($this->rowSelector, $row) . $this->activeButtonSelector,
            Locator::SELECTOR_XPATH
        );
        if ($element->isVisible()) {
            $element->click();
        }
    }
}
