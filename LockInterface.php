<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Class Lock to handle message lock transactions.
 */
interface LockInterface
{
    /**
     * Get lock id
     *
     * @return int
     */
    public function getId();

    /**
     * Set lock id
     *
     * @param int $value
     * @return void
     */
    public function setId($value);

    /**
     * Get message code
     *
     * @return string
     */
    public function getMessageCode();

    /**
     * Set message code
     *
     * @param string $value
     * @return void
     */
    public function setMessageCode($value);

    /**
     * Get lock date
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set lock date
     *
     * @param string $value
     * @return void
     */
    public function setCreatedAt($value);
}
