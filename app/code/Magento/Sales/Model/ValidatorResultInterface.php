<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
