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
interface LockInterface
{
    /**
     * Get lock id
     *
     * @return int
     * @since 2.1.0
     */
    public function getId();

    /**
     * Set lock id
     *
     * @param int $value
     * @return void
     * @since 2.1.0
     */
    public function setId($value);

    /**
     * Get message code
     *
     * @return string
     * @since 2.1.0
     */
    public function getMessageCode();

    /**
     * Set message code
     *
     * @param string $value
     * @return void
     * @since 2.1.0
     */
    public function setMessageCode($value);

    /**
     * Get lock date
     *
     * @return string
     * @since 2.1.0
     */
    public function getCreatedAt();

    /**
     * Set lock date
     *
     * @param string $value
     * @return void
     * @since 2.1.0
     */
    public function setCreatedAt($value);
}
