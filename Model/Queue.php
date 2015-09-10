<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MysqlMq\Model;

/**
 * Message model for queue based on MySQL.
 */
class Queue extends \Magento\Framework\Model\AbstractModel
{
    const KEY_NAME = 'name';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magento\MysqlMq\Model\Resource\Queue');
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
