<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page\Edit;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Widget\Test\Block\Adminhtml\WidgetForm;
use Magento\Cms\Test\Block\Adminhtml\Wysiwyg\Config;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Backend Cms Page edit page.
 */
class PageForm extends Form
{
    /**
     * Content Editor toggle button id.
     *
     * @var string
     */
    protected $toggleButton = "#togglepage_content";

    /**
     * Content Editor form.
     *
     * @var string
     */
    protected $contentForm = "#page_content";
    /**
     * System Variable block selector.
     *
     * @var string
     */
    protected $systemVariableBlock = "./ancestor::body//div[div[@id='variables-chooser']]";

    /**
     * Widget block selector.
     *
     * @var string
     */
    protected $widgetBlock = "//body//aside[div//*[@id='widget_options_form']]";

    /**
     * Insert Variable button selector.
     *
     * @var string
     */
    protected $addVariableButton = ".add-variable";

    /**
     * Insert Widget button selector.
     *
     * @var string
     */
    protected $addWidgetButton = '.action-add-widget';

    /**
     * Content input locator.
     *
     * @var string
     */
    protected $content = '#contentEditor';

    /**
     * Content Heading input locator.
     *
     * @var string
     */
    protected $contentHeading = '[name="content_heading"]';

    /**
     * Collapsible Elements locator.
     *
     * @var string
     */
    protected $collapsibleElements = '.admin__collapsible-title';

    /**
     * Magento form loader.
     *
     * @var string
     */
    protected $spinner = '[data-role="spinner"]';

    /**
     * Customer form to load.
     *
     * @var string
     */
    protected $activeFormTab = '.form-inline';

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Page Content Show/Hide Editor toggle button.
     *
     * @return void
     */
    protected function toggleEditor()
    {
        $content = $this->_rootElement->find($this->contentForm, Locator::SELECTOR_CSS);
        $toggleButton = $this->_rootElement->find($this->toggleButton, Locator::SELECTOR_CSS);
        if (!$content->isVisible() && $toggleButton->isVisible()) {
            $toggleButton->click();
        }
    }

    /**
     * Returns array with System Variables.
     *
     * @return array
     */
    public function getSystemVariables()
    {
        $this->clickInsertVariable();
        $config = $this->getWysiwygConfig();

        return $config->getAllVariables();
    }

    /**
     * Clicking in content tab 'Insert Variable' button.
     *
     * @param SimpleElement $element [optional]
     * @return void
     */
    public function clickInsertVariable(SimpleElement $element = null)
    {
        $context = $element === null ? $this->_rootElement : $element;
        $addVariableButton = $context->find($this->addVariableButton);
        if ($addVariableButton->isVisible()) {
            $addVariableButton->click();
        }
    }

    /**
     * Clicking in content tab 'Insert Widget' button.
     *
     * @param SimpleElement $element [optional]
     * @return void
     */
    public function clickInsertWidget(SimpleElement $element = null)
    {
        $context = $element === null ? $this->_rootElement : $element;
        $addWidgetButton = $context->find($this->addWidgetButton);
        if ($addWidgetButton->isVisible()) {
            $addWidgetButton->click();
        }
    }

    /**
     * Get for wysiwyg config block.
     *
     * @return Config
     */
    public function getWysiwygConfig()
    {
        return $this->blockFactory->create(
            'Magento\Cms\Test\Block\Adminhtml\Wysiwyg\Config',
            ['element' => $this->_rootElement->find($this->systemVariableBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Get widget block.
     *
     * @return WidgetForm
     */
    public function getWidgetBlock()
    {
        return $this->blockFactory->create(
            'Magento\Widget\Test\Block\Adminhtml\WidgetForm',
            ['element' => $this->_rootElement->find($this->widgetBlock, Locator::SELECTOR_XPATH)]
        );
    }

    /**
     * Wait for User before fill form which calls JS validation on correspondent form.
     *
     * @return void
     */
    public function waitForm()
    {
        $this->waitForElementNotVisible($this->spinner);
        $this->waitForElementVisible($this->activeFormTab);
    }

    /**
     * Show collapsible elements.
     *
     * @param SimpleElement $element [optional]
     * @return void
     */
    public function showCollapsible(SimpleElement $element = null)
    {
        $context = $element === null ? $this->_rootElement : $element;
        $collapsibleElements = $context->getElements($this->collapsibleElements);
        foreach ($collapsibleElements as $collapsibleElement) {
            if ($collapsibleElement->isVisible()) {
                $collapsibleElement->click();
            }
        }
    }

    /**
     * Fill data to content fields on content tab.
     *
     * @param FixtureInterface $fixture
     * @param SimpleElement|null $element
     * @return $this
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function fill(FixtureInterface $fixture, SimpleElement $element = null)
    {
        $this->waitForm();
        $this->showCollapsible();
        $data = $fixture->getData();
        $fields = isset($data['fields']) ? $data['fields'] : $data;
        $context = $element === null ? $this->_rootElement : $element;
        $context->find($this->content)->setValue($fields['content']['content']);
        if (isset($fields['content_heading'])) {
            $element->find($this->contentHeading)->setValue($fields['content_heading']);
        }
        if (isset($fields['content']['widget']['dataset'])) {
            foreach ($fields['content']['widget']['dataset'] as $widget) {
                $this->clickInsertWidget();
                $this->getWidgetBlock()->addWidget($widget);
            }
        }
        if (isset($fields['content']['variable'])) {
            $this->clickInsertVariable();
            $config = $this->getWysiwygConfig();
            $config->selectVariableByName($fields['content']['variable']);
        }
        unset($fields['content']);
        $mapping = $this->dataMapping($fields);
        $this->_fill($mapping, $element);

        return $this;
    }
}
