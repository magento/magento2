<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget;

use Magento\Backend\Test\Block\Widget\Grid as AbstractGrid;
use Magento\Mtf\Client\Locator;

/**
 * Widget grid on the Widget Instance Index page.
 */
class WidgetGrid extends AbstractGrid
{
    /**
     * Selector for not empty options at select element.
     *
     * @var string
     */
    private $notEmptyOptionsSelector = 'option:not([value=""])';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'tbody tr td.col-title';

    /**
     * First row selector.
     *
     * @var string
     */
    protected $firstRowSelector = '//tbody//tr[@data-role="row"]/td[contains(@class, "col-title")][1]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'title' => [
            'selector' => 'input[name="title"]',
        ],
        'theme_id' => [
            'selector' => 'select[name="theme_id"]',
            'input' => 'select',
        ],
    ];

    /**
     * Returns values of theme_id filter.
     *
     * @return array
     */
    public function getThemeIdValues()
    {
        $values = [];
        $themeFilter = $this->filters['theme_id'];
        $strategy = empty($themeFilter['strategy']) ? Locator::SELECTOR_CSS : $themeFilter['strategy'];
        $element = $this->_rootElement->find($themeFilter['selector'], $strategy, $themeFilter['input']);
        $options = $element->getElements($this->notEmptyOptionsSelector);
        foreach ($options as $option) {
            $values[] = $option->getText();
        }

        return $values;
    }
}
