<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model\ResourceModel;

/**
 * Sales report resource model
 */
class Report extends \Magento\Sales\Model\ResourceModel\EntityAbstract
{
    /**
     * Resource initialization
     *
     * @return void
     * phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     */
    protected function _construct()
    {
    }

    /**
     * Set main table and idField
     *
     * @param string $table
     * @param string $field
     * @return $this
     */
    public function init($table, $field = 'id')
    {
        $this->_init($table, $field);
        return $this;
    }
}
