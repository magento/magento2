<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

/**
 * Interface ValidatorResultInterface
 * @api
 */
interface ValidatorResultInterface
{
    /**
     * @param string $message
     * @return void
     */
    public function addMessage($message);

    /**
     * @return bool
     */
    public function hasMessages();

    /**
     * @return \string[]
     */
    public function getMessages();
}
