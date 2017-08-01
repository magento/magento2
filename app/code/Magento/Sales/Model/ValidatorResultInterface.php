<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Interface ValidatorResultInterface
 * @api
 * @since 2.2.0
 */
interface ValidatorResultInterface
{
    /**
     * @param string $message
     * @return void
     * @since 2.2.0
     */
    public function addMessage($message);

    /**
     * @return bool
     * @since 2.2.0
     */
    public function hasMessages();

    /**
     * @return \string[]
     * @since 2.2.0
     */
    public function getMessages();
}
