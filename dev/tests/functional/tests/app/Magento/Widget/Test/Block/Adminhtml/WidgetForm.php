<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml;

use Magento\Mtf\Block\Form;
use Magento\Mtf\Client\Locator;

/**
 * Backend add widget block form.
 */
class WidgetForm extends Form
{
    /**
     * Widget type selector.
     *
     * @var string
     */
    protected $widgetType = '[name="widget_type"]';

    /**
     * Insert widget button selector.
     *
     * @var string
     */
    protected $insertButton = '#insert_button';

    /**
     * Magento varienLoader.js loader.
     *
     * @var string
     */
    protected $loaderOld = '//ancestor::body/div[@id="loading-mask"]';

    /**
     * Add widgets.
     *
     * @param array $widget
     * @return void
     */
    public function addWidget($widget)
    {
        $this->selectWidgetType($widget['widget_type']);
        $mapping = $this->dataMapping($widget);
        $this->_fill($mapping);
        $this->insertWidget();
    }

    /**
     * Select widget type.
     *
     * @param string $type
     * @return void
     */
    protected function selectWidgetType($type)
    {
        $this->_rootElement->find($this->widgetType, Locator::SELECTOR_CSS, 'select')->setValue($type);
        $this->waitForElementNotVisible($this->loaderOld, Locator::SELECTOR_XPATH);
    }

    /**
     * Click Insert Widget button.
     *
     * @return void
     */
    protected function insertWidget()
    {
        $this->_rootElement->find($this->insertButton)->click();
        $this->waitForElementNotVisible($this->loaderOld, Locator::SELECTOR_XPATH);
    }
}
