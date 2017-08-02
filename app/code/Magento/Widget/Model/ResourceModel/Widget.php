<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Model\ResourceModel;

/**
 * Resource model for widget.
 *
 * @deprecated 2.2.0 Data from this table was moved to xml(widget.xml).
 * @since 2.0.0
 */
class Widget extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init('widget', 'widget_id');
    }

    /**
     * Retrieves pre-configured parameters for widget
     *
     * @param int $widgetId
     * @return array|false
     * @since 2.0.0
     */
    public function loadPreconfiguredWidget($widgetId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            $this->getIdFieldName() . '=:' . $this->getIdFieldName()
        );
        $bind = [$this->getIdFieldName() => $widgetId];
        $widget = $connection->fetchRow($select, $bind);
        if (is_array($widget)) {
            if ($widget['parameters']) {
                $widget['parameters'] = $this->getSerializer()->unserialize($widget['parameters']);
            }
            return $widget;
        }
        return false;
    }
}
