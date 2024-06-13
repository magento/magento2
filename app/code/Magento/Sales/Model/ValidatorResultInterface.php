<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Model;

/**
 * Interface ValidatorResultInterface
 * @api
 * @since 100.1.3
 */
interface ValidatorResultInterface
{
    /**
     * @param string $message
     * @return void
     * @since 100.1.3
     */
    public function addMessage($message);

    /**
     * @return bool
     * @since 100.1.3
     */
    public function hasMessages();

    /**
     * @return \string[]
     * @since 100.1.3
     */
    public function getMessages();
}
