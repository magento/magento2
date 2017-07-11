<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations;

use Magento\Backend\Test\Block\Template;
use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml catalog super product configurable section.
 */
class Config extends Section
{
    /** @var string */
    protected $createConfigurationsButton = '[data-index="create_configurable_products_button"] > span';

    /**
     * Selector for trigger show/hide "Variations" tab.
     *
     * @var string
     */
    protected $variationsTabTrigger = '[data-tab=super_config] [data-role=trigger]';

    /**
     * Selector for content "Variations" tab.
     *
     * @var string
     */
    protected $variationsTabContent = '#super_config-content';

    /**
     * Selector for button "Generate Products".
     *
     * @var string
     */
    protected $generateVariations = '[data-role=step-wizard-next] button';

    /**
     * Selector for variations matrix.
     *
     * @var string
     */
    protected $variationsMatrix = 'div[data-index="configurable-matrix"]';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Variations content selector.
     *
     * @var string
     */
    protected $variationsContent = '#product_info_tabs_super_config_content';

    /**
     * Locator for Configurations section.
     *
     * @var string
     */
    private $configurationsSection = '[data-index="configurable"]';

    /**
     * Fill variations fieldset.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        $attributes = isset($fields['configurable_attributes_data']['source'])
            ? $fields['configurable_attributes_data']['value']
            : [];

        $attributeSource = isset($fields['configurable_attributes_data']['source'])
            ? $fields['configurable_attributes_data']['source']
            : null;
        $attributesValue = $attributeSource !== null ? $attributeSource->getAttributesData() : [];

        foreach ($attributesValue as $key => $value) {
            $attributesValue[$key] = array_merge($value, $attributes['attributes_data'][$key]);
        }
        $this->createConfigurations();
        $this->getAttributeBlock()->fillAttributes($attributesValue, $attributeSource);
        if (!empty($attributes['matrix'])) {
            $this->generateVariations();
            $this->getVariationsBlock()->fillVariations($attributes['matrix']);
        }

        return $this;
    }

    /**
     * Click 'Create Configurations' button.
     *
     * @return void
     */
    public function createConfigurations()
    {
        $this->_rootElement->find($this->configurationsSection)->hover();
        $this->_rootElement->find($this->createConfigurationsButton)->click();
    }

    /**
     * Generate variations.
     *
     * @return void
     */
    public function generateVariations()
    {
        $this->browser->find($this->generateVariations)->click();
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Get block of attributes.
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Attribute
     */
    public function getAttributeBlock()
    {
        return $this->blockFactory->create(
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Attribute::class,
            ['element' => $this->_rootElement]
        );
    }

    /**
     * Get block of variations.
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Matrix
     */
    public function getVariationsBlock()
    {
        return $this->blockFactory->create(
            \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Section\Variations\Config\Matrix::class,
            ['element' => $this->_rootElement->find($this->variationsMatrix)]
        );
    }

    /**
     * Get template block.
     *
     * @return Template
     */
    public function getTemplateBlock()
    {
        return $this->blockFactory->create(
            \Magento\Backend\Test\Block\Template::class,
            ['element' => $this->_rootElement->find($this->template, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get data of tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $data = [];
        $data['matrix'] = $this->getVariationsBlock()->getVariationsData();

        return ['configurable_attributes_data' => $data];
    }

    /**
     * Delete all attributes.
     *
     * @return void
     */
    public function deleteVariations()
    {
        $this->getVariationsBlock()->deleteVariations();
    }
}
