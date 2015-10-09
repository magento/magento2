<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ModelResource\Type;

abstract class Db extends \Magento\Framework\Model\ModelResource\Type\AbstractType
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_entityClass = 'Magento\Framework\Model\ModelResource\Entity\Table';
    }
}
