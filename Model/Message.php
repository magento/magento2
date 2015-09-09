<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Model;

/**
 * Message model for queue based on MySQL.
 */
class Message extends \Magento\Framework\Model\AbstractModel
{
    const KEY_BODY = 'body';
    const KEY_TOPIC_NAME = 'topic_name';
    const KEY_STATUS = 'status';
    const KEY_UPDATED_AT = 'updated_at';

    const STATUS_NEW = 2;
    const STATUS_COMPLETE= 3;
    const STATUS_ERROR = 4;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magento\MysqlMq\Model\Resource\Message');
    }

    /**
     * Get message body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->getData(self::KEY_BODY);
    }

    /**
     * Get message topic name.
     *
     * @return string
     */
    public function getTopicName()
    {
        return $this->getData(self::KEY_TOPIC_NAME);
    }

    /**
     * Get message status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::KEY_STATUS);
    }

    /**
     * Get last modification date.
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::KEY_UPDATED_AT);
    }
}
