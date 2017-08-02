<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\ResourceModel\Template;

/**
 * Templates collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Template table name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_templateTable;

    /**
     * Define resource table
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        $this->_init(\Magento\Email\Model\Template::class, \Magento\Email\Model\ResourceModel\Template::class);
        $this->_templateTable = $this->getMainTable();
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('template_id', 'template_code');
    }
}
