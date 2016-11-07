<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block;

use Magento\Widget\Test\Fixture\Widget;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;

/**
 * Widget block on the frontend.
 */
class WidgetView extends Block
{
    /**
     * Widgets link selector.
     *
     * @var string
     */
    protected $widgetLinkSelector = '//a[contains(.,"%s")]';

    /**
     * Widgets selector.
     *
     * @var string
     */
    protected $widgetSelector = '//div[contains(.,"%s")]';

    /**
     * Check is visible widget selector.
     *
     * @param Widget $widget
     * @param string $widgetText
     * @return bool
     * @throws \Exception
     */
    public function isWidgetVisible(Widget $widget, $widgetText)
    {
        $widgetType = $this->getWidgetType($widget);
        if ($this->hasRender($widgetType)) {
            return $this->callRender(
                $widgetType,
                'isWidgetVisible',
                ['widget' => $widget, 'widgetText' => $widgetText]
            );
        } else {
            if (isset($this->widgetSelector)) {
                return $this->_rootElement->find(
                    sprintf($this->widgetSelector, $widgetText),
                    Locator::SELECTOR_XPATH
                )->isVisible();
            } else {
                throw new \Exception('Determine how to find the widget on the page.');
            }
        }
    }

    /**
     * Click to widget selector.
     *
     * @param Widget $widget
     * @param string $widgetText
     * @return void
     * @throws \Exception
     */
    public function clickToWidget(Widget $widget, $widgetText)
    {
        $widgetType = $this->getWidgetType($widget);
        if ($this->hasRender($widgetType)) {
            $this->callRender($widgetType, 'clickToWidget', ['widget' => $widget, 'widgetText' => $widgetText]);
        } else {
            if (isset($this->widgetLinkSelector)) {
                $this->_rootElement->find(
                    sprintf($this->widgetLinkSelector, $widgetText),
                    Locator::SELECTOR_XPATH
                )->click();
            } else {
                throw new \Exception('Determine how to find the widget on the page.');
            }
        }
    }

    /**
     * Get widget type based on widget code.
     *
     * @param Widget $widget
     * @return string
     */
    protected function getWidgetType(Widget $widget)
    {
        return lcfirst(str_replace(' ', '', ucwords(strtolower($widget->getCode()))));
    }
}
