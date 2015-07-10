<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config;

use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute\AttributeSelector;
use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\ObjectManager;

/**
 * Attribute block in Variation section.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Attribute extends Form
{
    /**
     * Mapping fields for get values of form
     *
     * @var array
     */
    protected $mappingGetFields = [
        'label' => [
            'selector' => 'td[data-column="name"]',
            'strategy' => Locator::SELECTOR_CSS,
        ],
    ];

    /**
     * Variation search block
     *
     * @var string
     */
    protected $variationSearchBlock = '#variations-search-field';

    /**
     * Selector for "Create New Variations Set"
     *
     * @var string
     */
    protected $createNewVariationSet = '[title="Create New Attribute"]';

    /**
     * New attribute frame selector
     *
     * @var string
     */
    protected $newAttributeFrame = '#create_new_attribute_container';

    /**
     * Selector for "New Attribute" block
     *
     * @var string
     */
    protected $newAttribute = 'body';

    /**
     * Selector for "Save Attribute" button on "New Attribute" dialog window
     *
     * @var string
     */
    protected $saveAttribute = '[data-ui-id="attribute-edit-content-save-button"]';

    /**
     * Selector attribute block by label
     *
     * @var string
     */
    protected $attributeBlockByName = '[data-attribute-title="%s"]';

    /**
     * Selector attribute value by label
     *
     * @var string
     */
    protected $attributeOptionByName = '[data-attribute-option-title="%s"]';

    /**
     * Selector for attribute block
     *
     * @var string
     */
    protected $attributeBlock = '//div[@id="configurable-attributes-container"]/div[contains(@class,"entry-edit")][%d]';

    /**
     * Selector for "Create New Value" button
     *
     * @var string
     */
    protected $addOption = '[data-action=addOption]';

    /**
     * Selector for "Next" button in wizard
     *
     * @var string
     */
    protected $nextButton = '[data-role=step-wizard-next]';

    /**
     * Selector for option container
     *
     * @var string
     */
    protected $optionContainer = './/tr[@data-role="option-container"]';

    /**
     * Selector for option container(row) by number
     *
     * @var string
     */
    protected $optionContainerByNumber = './/tr[@data-role="option-container"][%d]';

    /**
     * Selector for attribute title
     *
     * @var string
     */
    protected $attributeTitle = '[data-toggle="collapse"]';

    /**
     * Selector for attribute content
     *
     * @var string
     */
    protected $attributeContent = '[id$="-content"]';

    /**
     * Selector for attribute label
     *
     * @var string
     */
    protected $attributeLabel = '[name$="[label]"]';

    /**
     * Config content selector
     *
     * @var string
     */
    protected $configContent = '#super_config-content';

    /**
     * Fill attributes
     *
     * @param array $attributes
     * @return void
     */
    public function fillAttributes(array $attributes)
    {
        $grid = $this->getAttributesGrid();
        foreach ($attributes as $attribute) {
            $isExistAttribute = true;
            try {
                $grid->searchAndSelect(['frontend_label' => $attribute['frontend_label']]);
            } catch (\Exception $e) {
                if ($e->getMessage() === 'Searched item was not found.') {
                    $isExistAttribute = false;
                } else {
                    throw $e;
                }
            }

            if (!$isExistAttribute && empty($attribute['attribute_id'])) {
                $this->createNewVariationSet($attribute);
                $this->waitBlock($this->newAttributeFrame);
                $grid->searchAndSelect(['frontend_label' => $attribute['frontend_label']]);
            } else {
                if (!$isExistAttribute) {
                    $this->getAttributeSelector()->setValue($attribute['frontend_label']);
                }
            }
        }

        $this->browser->find($this->nextButton)->click();

        foreach ($attributes as $attribute) {
            $this->updateOptions($attribute);
        }

        $this->browser->find($this->nextButton)->click();
        $this->browser->find($this->nextButton)->click();
    }

    /**
     * @return \Magento\Ui\Test\Block\Adminhtml\DataGrid
     */
    protected function getAttributesGrid()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AttributesGrid',
            ['element' => $this->browser->find('.admin__data-grid-outer-wrap')]
        );
    }

    /**
     * Create new variation set.
     *
     * @param array $attribute
     * @return void
     */
    protected function createNewVariationSet(array $attribute)
    {
        $attributeFixture = ObjectManager::getInstance()->create(
            'Magento\Catalog\Test\Fixture\CatalogProductAttribute',
            ['data' => $attribute]
        );

        $this->browser->find($this->createNewVariationSet)->click();
        $this->getEditAttributeForm()->fill($attributeFixture);
        $this->getEditAttributeForm()->saveAttributeForm();
    }

    /**
     * Wait that element is not visible.
     *
     * @param string $selector
     * @param mixed $browser [optional]
     * @param string $strategy [optional]
     * @return mixed
     */
    protected function waitBlock($selector, $browser = null, $strategy = Locator::SELECTOR_CSS)
    {
        $browser = ($browser != null) ? $browser : $this->browser;
        return $browser->waitUntil(
            function () use ($browser, $selector, $strategy) {
                return $browser->find($selector, $strategy)->isVisible() == false ? true : null;
            }
        );
    }

    /**
     * Fill options
     *
     * @param array $attribute
     * @return void
     */
    protected function fillOptions(array $attribute)
    {
        $attributeBlock = $this->browser->find(
            sprintf($this->attributeBlockByName, $attribute['frontend_label']),
            Locator::SELECTOR_XPATH
        );
        $count = 0;

        $this->showAttributeContent($attributeBlock);
        if (isset($attribute['label'])) {
            $attributeBlock->find($this->attributeLabel)->setValue($attribute['label']);
        }
        foreach ($attribute['options'] as $option) {
            $attributeBlock->find($this->addOption)->click();
            $count++;
            $optionContainer = $attributeBlock->find(
                sprintf($this->optionContainerByNumber, $count),
                Locator::SELECTOR_XPATH
            );

            $mapping = $this->dataMapping($option);
            $this->_fill($mapping, $optionContainer);
        }
    }

    /**
     * Update options
     *
     * @param array $attribute
     * @return void
     */
    protected function updateOptions(array $attribute)
    {
        $attributeBlock = $this->browser->find(sprintf($this->attributeBlockByName, $attribute['frontend_label']));
        $count = 0;

        if (isset($attribute['label'])) {
            // label is not editable anymore
            // $attributeBlock->find($this->attributeLabel)->setValue($attribute['label']);
        }
        foreach ($attribute['options'] as $option) {
            $count++;
            $optionContainer = $attributeBlock->find(sprintf($this->attributeOptionByName, $option['label']));

            if (!$optionContainer->isVisible()) {
                $mapping = $this->dataMapping($option);
                $attributeBlock->find($this->addOption)->click();

                $optionContainer = $attributeBlock->find('[data-attribute-option-title=""]');

                $this->getElement($optionContainer, $mapping['label'])
                    ->setValue($mapping['label']['value']);

                $optionContainer->find('[data-action=save]')->click();
            }
        }
    }

    /**
     * Check is visible option
     *
     * @param SimpleElement $attributeBlock
     * @param int $number
     * @return bool
     */
    protected function isVisibleOption(SimpleElement $attributeBlock, $number)
    {
        return $attributeBlock->find(
            sprintf($this->optionContainerByNumber, $number),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Get attribute form block
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm
     */
    protected function getEditAttributeForm()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Adminhtml\Product\Attribute\AttributeForm',
            ['element' => $this->browser->find($this->newAttribute)]
        );
    }

    /**
     * Get attribute selector element
     *
     * @return AttributeSelector
     */
    public function getAttributeSelector()
    {
        return $this->_rootElement->find(
            $this->variationSearchBlock,
            Locator::SELECTOR_CSS,
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super\Config\Attribute\AttributeSelector'
        );
    }

    /**
     * Get attributes data
     *
     * @return array
     */
    public function getAttributesData()
    {
        $data = [];
        $optionMapping = $this->dataMapping();

        $count = 1;
        /** @var SimpleElement $attributeBlock */
        $attributeBlock = $this->_rootElement->find(sprintf($this->attributeBlock, $count), Locator::SELECTOR_XPATH);
        while ($attributeBlock->isVisible()) {
            $this->showAttributeContent($attributeBlock);
            $attribute = [
                'frontend_label' => $attributeBlock->find($this->attributeTitle)->getText(),
                'label' => $attributeBlock->find($this->attributeLabel)->getValue(),
                'options' => [],
            ];
            $options = $attributeBlock->getElements($this->optionContainer, Locator::SELECTOR_XPATH);
            foreach ($options as $optionKey => $option) {
                /** @var SimpleElement $option */
                if ($option->isVisible()) {
                    $attribute['options'][$optionKey] = $this->_getData($optionMapping, $option);
                    $attribute['options'][$optionKey] += $this->getOptionalFields($option);
                }
            }
            $data[] = $attribute;

            ++$count;
            $attributeBlock = $this->_rootElement->find(
                sprintf($this->attributeBlock, $count),
                Locator::SELECTOR_XPATH
            );
        }

        return $data;
    }

    /**
     * Show attribute content
     *
     * @param SimpleElement $attribute
     * @return void
     */
    protected function showAttributeContent(SimpleElement $attribute)
    {
        if (!$attribute->find($this->attributeContent)->isVisible()) {
            $this->_rootElement->find($this->configContent)->click();
            $attribute->find($this->attributeTitle)->click();

            $browser = $attribute;
            $selector = $this->attributeContent;
            $browser->waitUntil(
                function () use ($browser, $selector) {
                    return $browser->find($selector)->isVisible() ? true : null;
                }
            );
        }
    }

    /**
     * Check exist attribute by label
     *
     * @param string $label
     * @return bool
     */
    protected function isExistAttribute($label)
    {
        return $this->_rootElement->find(
            sprintf($this->attributeBlockByName, $label),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Get optional fields
     *
     * @param SimpleElement $context
     * @param array $fields
     * @return array
     */
    protected function getOptionalFields(SimpleElement $context, array $fields = [])
    {
        $data = [];

        $fields = empty($fields) ? $this->mappingGetFields : $fields;
        foreach ($fields as $name => $params) {
            $data[$name] = $context->find($params['selector'], $params['strategy'])->getText();
        }

        return $data;
    }
}
