<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Type;

abstract class Db extends \Magento\Framework\Model\ResourceModel\Type\AbstractType
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_entityClass = 'Magento\Framework\Model\ResourceModel\Entity\Table';
    }
}
