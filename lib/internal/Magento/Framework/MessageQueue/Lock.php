<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Class Lock to handle message lock transactions.
 */
class Lock extends \Magento\Framework\DataObject implements LockInterface
{
    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * @inheritDoc
     */
    public function setId($value)
    {
        $this->setData('id', $value);
    }

    /**
     * @inheritDoc
     */
    public function getMessageCode()
    {
        return $this->getData('message_code');
    }

    /**
     * @inheritDoc
     */
    public function setMessageCode($value)
    {
        $this->setData('message_code', $value);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($value)
    {
        $this->setData('created_at', $value);
    }
}
