<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations;

use Magento\Backend\Test\Block\Template;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * Adminhtml catalog super product configurable tab.
 */
class Config extends Tab
{
    /** @var string */
    protected $createConfigurationsButton = '[data-action=open-steps-wizard]';

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
    protected $variationsMatrix = '[data-role="product-variations-matrix"]';

    /**
     * Selector for template block.
     *
     * @var string
     */
    protected $template = './ancestor::body';

    /**
     * Attribute element selector.
     *
     * @var string
     */
    protected $attributeElement = 'tr[data-row-number]';

    /**
     * Delete variation button selector.
     *
     * @var string
     */
    protected $deleteVariation = '[data-bind*="removeProduct"]';

    /**
     * Action menu
     *
     * @var string
     */
    protected $actionMenu = '.action-select';

    /**
     * Variations content selector.
     *
     * @var string
     */
    protected $variationsContent = '#product_info_tabs_super_config_content';

    /**
     * Fill variations fieldset.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $attributes = isset($fields['configurable_attributes_data']['source'])
            ? $fields['configurable_attributes_data']['value']
            : [];

        $this->showContent();
        $attributesValue = isset($fields['configurable_attributes_data']['source'])
            ? $fields['configurable_attributes_data']['source']->getAttributesData()
            : [];
        foreach ($attributesValue as $key => $value) {
            $attributesValue[$key] = array_merge($value, $attributes['attributes_data'][$key]);
        }
        $this->createConfigurations();
        $this->getAttributeBlock()->fillAttributes($attributesValue);
        if (!empty($attributes['matrix'])) {
            $this->generateVariations();
            $this->getVariationsBlock()->fillVariations($attributes['matrix']);
        }

        return $this;
    }

    /**
     * Show "Variations" tab content.
     *
     * @return void
     */
    public function showContent()
    {
        $content = $this->_rootElement->find($this->variationsTabContent);
        if (!$content->isVisible()) {
            $this->_rootElement->find($this->variationsTabTrigger)->click();
            $this->waitForElementVisible($this->variationsTabContent);
        }
    }

    /**
     * Click 'Create Configurations' button.
     *
     * @return void
     */
    public function createConfigurations()
    {
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
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Attribute
     */
    public function getAttributeBlock()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Attribute',
            ['element' => $this->_rootElement]
        );
    }

    /**
     * Get block of variations.
     *
     * @return \Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix
     */
    public function getVariationsBlock()
    {
        return $this->blockFactory->create(
            'Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Variations\Config\Matrix',
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
            'Magento\Backend\Test\Block\Template',
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
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $data = [];

        $this->showContent();
        $data['matrix'] = $this->getVariationsBlock()->getVariationsData();

        return ['configurable_attributes_data' => $data];
    }

    /**
     * Delete all attributes.
     *
     * @return void
     */
    public function deleteAttributes()
    {
        $attributeElements = $this->_rootElement->getElements($this->attributeElement);
        foreach (array_reverse($attributeElements) as $element) {
            $element->find($this->actionMenu)->hover();
            $element->find($this->actionMenu)->click();
            $element->find($this->deleteVariation)->click();
        }
    }
}
