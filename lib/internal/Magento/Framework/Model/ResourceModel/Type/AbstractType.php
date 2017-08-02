<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Type;

/**
 * Class \Magento\Framework\Model\ResourceModel\Type\AbstractType
 *
 * @since 2.0.0
 */
abstract class AbstractType
{
    /**
     * Name
     *
     * @var String
     * @since 2.0.0
     */
    protected $_name = '';

    /**
     * Entity class
     *
     * @var String
     * @since 2.0.0
     */
    protected $_entityClass = \Magento\Framework\Model\ResourceModel\Entity\AbstractEntity::class;

    /**
     * Retrieve entity type
     *
     * @return String
     * @since 2.0.0
     */
    public function getEntityClass()
    {
        return $this->_entityClass;
    }

    /**
     * Set name
     *
     * @param String $name
     * @return void
     * @since 2.0.0
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Retrieve name
     *
     * @return String
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->_name;
    }
}
