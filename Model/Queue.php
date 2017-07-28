<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Message model for queue based on MySQL.
 *
 * @api
 * @since 2.0.0
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    const KEY_NAME = 'name';

    /**
     * {@inheritdoc}
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getName()
    {
        return $this->getData(self::KEY_NAME);
    }
}
