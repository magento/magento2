<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\ResourceModel\Template;

/**
 * Templates collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Template table name
     *
     * @var string
     */
    protected $_templateTable;

    /**
     * Define resource table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Magento\Email\Model\Template', 'Magento\Email\Model\ResourceModel\Template');
        $this->_templateTable = $this->getMainTable();
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('template_id', 'template_code');
    }
}
