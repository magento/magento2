<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Class Lock to handle message lock transactions.
 * @since 2.1.0
 */
class Lock extends \Magento\Framework\DataObject implements LockInterface
{
    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setId($value)
    {
        $this->setData('id', $value);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getMessageCode()
    {
        return $this->getData('message_code');
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setMessageCode($value)
    {
        $this->setData('message_code', $value);
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    /**
     * @inheritDoc
     * @since 2.1.0
     */
    public function setCreatedAt($value)
    {
        $this->setData('created_at', $value);
    }
}
