<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\MysqlMq\Model;

/**
 * Message model for queue based on MySQL.
 *
 * @api
 * @since 100.0.2
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    const KEY_NAME = 'name';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(\Magento\MysqlMq\Model\ResourceModel\Queue::class);
    }

    /**
     * Set queue name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->setData(self::KEY_NAME, $name);
        return $this;
    }

    /**
     * Get queue name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }
}
