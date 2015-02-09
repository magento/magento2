<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Resource;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Condition\ConditionInterface;

/**
 * Wee tax resource model
 */
class Tax extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     */
    public function __construct(\Magento\Framework\App\Resource $resource, \Magento\Framework\Stdlib\DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
        parent::__construct($resource);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('weee_tax', 'value_id');
    }

    /**
     * Fetch one
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     */
    public function fetchOne($select)
    {
        return $this->_getReadAdapter()->fetchOne($select);
    }
}
