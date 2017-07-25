<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config;

use Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Attribute\AttributeSelector;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct\ConfigurableAttributesData;
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

    // @codingStandardsIgnoreStart
    /**
     * Selector for attribute block
     *
     * @var string
     */
    protected $attributeBlock = '//div[@data-role="configurable-attributes-container"]/div[@data-role="attribute-info"][%d]';
    // @codingStandardsIgnoreEnd

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
    protected $nextButton = '[data-role=step-wizard-next] button';

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
     * Backend abstract block.
     *
     * @var string
     */
    protected $templateBlock = './ancestor::body';

    /**
     * List of selected attributes
     *
     * @var string
     */
    private $selectedAttributes = 'span[data-bind*="selectedAttributes"]';

    /**
     * Wizard Images step CSS selector.
     *
     * @var string
     */
    private $wizardImagesStep = '#variation-steps-wizard_step3';

    /**
     * Attributes grid spinner selector
     *
     * @var string
     */
    private $attributesGridSpinner = '.productFormConfigurable [data-role="spinner"]';

    /**
     * CSS Selector for attribute grid.
     *
     * @var string
     */
    private $attributesGridSelector = '#variation-steps-wizard_step1 .admin__data-grid-outer-wrap';

    /**
     * Fill attributes
     *
     * @param array $attributes
     * @param ConfigurableAttributesData $attributeSource
     * @return void
     */
    public function fillAttributes(array $attributes, ConfigurableAttributesData $attributeSource)
    {
        $attributesFilters = [];
        foreach ($attributes as $attribute) {
            if (empty($attribute['attribute_id'])) {
                $this->createNewVariationSet($attribute);
            }
            $attributesFilters[] = ['frontend_label' => $attribute['frontend_label']];
        }

        //select attributes
        $this->waitAttributesGridLoad();
        $this->getAttributesGrid()->resetFilter();
        $this->getAttributesGrid()->waitForElementNotVisible($this->attributesGridSpinner);
        $this->getTemplateBlock()->waitLoader();

        $attributesList = $this->browser->find($this->selectedAttributes)->getText();
        if (!$attributesList || $attributesList !== '--') {
            $this->getAttributesGrid()->deselectAttributes();
        }

        if ($this->_rootElement->find('[class$=no-data]')->isVisible()) {
            return;
        }
        $this->getAttributesGrid()->selectItems($attributesFilters);

        $this->browser->find($this->nextButton)->click();
        $this->getTemplateBlock()->waitLoader();

        //update attributes options
        foreach ($attributes as $attribute) {
            $this->updateOptions($attribute);
        }

        $this->browser->find($this->nextButton)->click();
        $this->fillBulkImagesPriceAndQuantity($attributeSource, $attributes);
        $this->getTemplateBlock()->waitLoader();
        $this->browser->find($this->nextButton)->click();
    }

    /**
     * Wait for 'Attributes Grid' loaded.
     *
     * @return void
     */
    private function waitAttributesGridLoad()
    {
        $this->waitForElementVisible($this->attributesGridSelector);
        $this->waitForElementNotVisible($this->attributesGridSpinner);
    }

    /**
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AttributesGrid
     */
    public function getAttributesGrid()
    {
        return $this->blockFactory->create(
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\AttributesGrid::class,
            ['element' => $this->browser->find($this->attributesGridSelector)]
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
            \Magento\Catalog\Test\Fixture\CatalogProductAttribute::class,
            ['data' => $attribute]
        );

        $browser = $this->browser;
        $createSetSelector = $this->createNewVariationSet;
        $browser->waitUntil(
            function () use ($browser, $createSetSelector) {
                return $browser->find($createSetSelector)->isVisible() ? true : null;
            }
        );

        $this->browser->find($this->createNewVariationSet)->click();
        $newAttributeForm = $this->getNewAttributeForm();
        $newAttributeForm->fill($attributeFixture);
        $newAttributeForm->saveAttributeForm();
        $this->waitBlock($this->newAttributeFrame);
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

        foreach ($attribute['options'] as $option) {
            $count++;
            $label = isset($option['admin']) ? $option['admin'] : $option['label'];
            $optionContainer = $attributeBlock->find(sprintf($this->attributeOptionByName, $label));

            //Create option
            if (!$optionContainer->isVisible()) {
                $mapping = $this->dataMapping($option);
                $attributeBlock->find($this->addOption)->click();

                $optionContainer = $attributeBlock->find('[data-attribute-option-title=""]');

                $this->getElement($optionContainer, $mapping['label'])
                    ->setValue($mapping['label']['value']);
                $this->getTemplateBlock()->waitLoader();
                $optionContainer->find('[data-action=save]')->click();
                $optionContainer = $attributeBlock->find(sprintf($this->attributeOptionByName, $label));
            }
            //Select option
            $isOptionSelected = $optionContainer->find('[type="checkbox"]')->isSelected();
            if (!$isOptionSelected && $option['include'] === 'Yes') {
                $optionContainer->find('[type="checkbox"]')->click();
            } elseif ($isOptionSelected && $option['include'] === 'No') {
                $optionContainer->find('[type="checkbox"]')->click();
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
     * Get new attribute form block.
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\NewConfigurableAttributeForm
     */
    protected function getNewAttributeForm()
    {
        return $this->blockFactory->create(
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\NewConfigurableAttributeForm::class,
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
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Attribute::class
            . '\AttributeSelector'
        );
    }

    /**
     * Get attributes data
     *
     * @deprecated
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

    /**
     * Get backend abstract block.
     *
     * @return \Magento\Backend\Test\Block\Template
     */
    protected function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->templateBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Fill Step 3: Bulk Images, Price and Quantity.
     *
     * @param ConfigurableAttributesData $attributeSource
     * @param array $attributes
     * @return void
     */
    private function fillBulkImagesPriceAndQuantity(ConfigurableAttributesData $attributeSource, array $attributes)
    {
        if (empty($attributeSource->getBulkImagesPriceQuantity())) {
            return;
        }

        $wizardStep = $this->browser->find($this->wizardImagesStep);
        $data = $this->prepareImageStepData($attributeSource->getBulkImagesPriceQuantity(), $attributes);
        $mapping = $this->dataMapping($data);
        $this->_fill($mapping, $wizardStep);
    }

    /**
     * Prepare data for Step 3: Bulk Images, Price and Quantity.
     *
     * @param array $data
     * @param array $attributes
     * @return array
     */
    private function prepareImageStepData(array $data, array $attributes)
    {
        if (isset($data['images'])) {
            $data['images']['image_attribute'] = $attributes['attribute_key_0']['attribute_code'];
            $data['images']['black_option_image'] = MTF_TESTS_PATH . array_shift($data['images']['images']);
            $data['images']['white_option_image'] = MTF_TESTS_PATH . array_shift($data['images']['images']);
            unset($data['images']['images']);
        }

        if (isset($data['price'])) {
            $data['price']['price_option'] = $attributes['attribute_key_1']['attribute_code'];
            ksort($data['price']);
        }

        return $data;
    }
}
