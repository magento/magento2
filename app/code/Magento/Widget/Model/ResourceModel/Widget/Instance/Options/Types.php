<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\ResourceModel\Widget\Instance\Options;

/**
 * Widget Instance Types Options
 * @since 2.0.0
 */
class Types implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Widget\Model\Widget\Instance
     * @since 2.0.0
     */
    protected $_model;

    /**
     * @param \Magento\Widget\Model\Widget\Instance $widgetInstanceModel
     * @since 2.0.0
     */
    public function __construct(\Magento\Widget\Model\Widget\Instance $widgetInstanceModel)
    {
        $this->_model = $widgetInstanceModel;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $widgets = [];
        $widgetsOptionsArr = $this->_model->getWidgetsOptionArray('type');
        foreach ($widgetsOptionsArr as $widget) {
            $widgets[$widget['value']] = $widget['label'];
        }
        return $widgets;
    }
}
