<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Model\ResourceModel\Entity;

/**
 * Class describing db table resource entity
 *
 */
class Table extends \Magento\Framework\Model\ResourceModel\Entity\AbstractEntity
{
    /**
     * Get table
     *
     * @return String
     */
    public function getTable()
    {
        return $this->getConfig('table');
    }
}
