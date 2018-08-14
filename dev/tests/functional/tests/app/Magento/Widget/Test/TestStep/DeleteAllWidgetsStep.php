<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\TestStep;

use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceEdit;
use Magento\Widget\Test\Page\Adminhtml\WidgetInstanceIndex;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Delete all widgets on backend
 */
class DeleteAllWidgetsStep implements TestStepInterface
{
    /**
     * WidgetInstanceIndex page
     *
     * @var WidgetInstanceIndex
     */
    protected $widgetInstanceIndex;

    /**
     * WidgetInstanceEdit page
     *
     * @var WidgetInstanceEdit
     */
    protected $widgetInstanceEdit;

    /**
     * @construct
     * @param WidgetInstanceIndex $widgetInstanceIndex
     * @param WidgetInstanceEdit $widgetInstanceEdit
     */
    public function __construct(
        WidgetInstanceIndex $widgetInstanceIndex,
        WidgetInstanceEdit $widgetInstanceEdit
    ) {
        $this->widgetInstanceIndex = $widgetInstanceIndex;
        $this->widgetInstanceEdit = $widgetInstanceEdit;
    }

    /**
     * Delete Widget on backend
     *
     * @return array
     */
    public function run()
    {
        $this->widgetInstanceIndex->open();
        $this->widgetInstanceIndex->getWidgetGrid()->resetFilter();
        while ($this->widgetInstanceIndex->getWidgetGrid()->isFirstRowVisible()) {
            $this->widgetInstanceIndex->getWidgetGrid()->openFirstRow();
            $this->widgetInstanceEdit->getTemplateBlock()->waitLoader();
            $this->widgetInstanceEdit->getPageActionsBlock()->delete();
            $this->widgetInstanceEdit->getModalBlock()->acceptAlert();
            $this->widgetInstanceIndex->getSystemMessageDialog()->closePopup();
        }
    }
}
