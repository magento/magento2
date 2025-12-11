<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Class Lock to handle message lock transactions.
 * @api
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
