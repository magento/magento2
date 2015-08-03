<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Block\Adminhtml\Page\Edit\Tab;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Widget\Test\Block\Adminhtml\WidgetForm;
use Magento\Cms\Test\Block\Adminhtml\Wysiwyg\Config;

/**
 * Backend cms page content tab.
 */
class Content extends Tab
{
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
    protected $widgetBlock = "./ancestor::body/div[div/div/*[@id='widget_options_form']]";

    /**
     * Insert Variable button selector.
     *
     * @var string
     */
    protected $addVariableButton = ".add-variable";

    /**
     * Insert Frontend App button selector.
     *
     * @var string
     */
    protected $addWidgetButton = '.action-add-widget';

    /**
     * Content input locator.
     *
     * @var string
     */
    protected $content = '#page_content';

    /**
     * Content Heading input locator.
     *
     * @var string
     */
    protected $contentHeading = '#page_content_heading';

    /**
     * Clicking in content tab 'Insert Variable' button.
     *
     * @return void
     */
    public function clickInsertVariable()
    {
        $addVariableButton = $this->_rootElement->find($this->addVariableButton);
        if ($addVariableButton->isVisible()) {
            $addVariableButton->click();
        }
    }

    /**
     * Clicking in content tab 'Insert Frontend App' button.
     *
     * @return void
     */
    public function clickInsertWidget()
    {
        $addWidgetButton = $this->_rootElement->find($this->addWidgetButton);
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
     * Fill data to content fields on content tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $fields, SimpleElement $element = null)
    {
        $element->find($this->content)->setValue($fields['content']['value']['content']);
        if (isset($fields['content_heading']['value'])) {
            $element->find($this->contentHeading)->setValue($fields['content_heading']['value']);
        }
        if (isset($fields['content']['value']['widget']['dataset'])) {
            foreach ($fields['content']['value']['widget']['dataset'] as $widget) {
                $this->clickInsertWidget();
                $this->getWidgetBlock()->addWidget($widget);
            }
        }
        if (isset($fields['content']['value']['variable'])) {
            $this->clickInsertVariable();
            $config = $this->getWysiwygConfig();
            $config->selectVariableByName($fields['content']['value']['variable']);
        }

        return $this;
    }

    /**
     * Get data of content tab.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        return [
            'content' => [],
            'content_heading' => ''
        ];
    }
}
