<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Type;

/**
 * Class \Magento\Framework\Model\ResourceModel\Type\Db
 *
 * @since 2.0.0
 */
abstract class Db extends \Magento\Framework\Model\ResourceModel\Type\AbstractType
{
    /**
     * Constructor
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->_entityClass = \Magento\Framework\Model\ResourceModel\Entity\Table::class;
    }
}
