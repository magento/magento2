<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Model\ResourceModel\Type;

abstract class Db extends \Magento\Framework\Model\ResourceModel\Type\AbstractType
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_entityClass = \Magento\Framework\Model\ResourceModel\Entity\Table::class;
    }
}
