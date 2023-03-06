<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\ResourceModel\Widget\Instance\Options;

use Magento\Framework\Option\ArrayInterface;
use Magento\Widget\Model\Widget\Instance;

/**
 * Widget Instance Types Options
 */
class Types implements ArrayInterface
{
    /**
     * @var Instance
     */
    protected $_model;

    /**
     * @param Instance $widgetInstanceModel
     */
    public function __construct(Instance $widgetInstanceModel)
    {
        $this->_model = $widgetInstanceModel;
    }

    /**
     * @return array
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
