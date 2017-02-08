<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Preconfigured widget
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Widget\Model\ResourceModel;

/**
 * Resource model for widget.
 *
 * @deprecated Data from this table was moved to xml(widget.xml).
 */
class Widget extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
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
