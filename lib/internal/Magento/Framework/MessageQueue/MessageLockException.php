<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class MessageLockException to be thrown when a message being processed is already in the lock table.
 *
 * @api
 * @since 100.1.0
 */
class MessageLockException extends LocalizedException
{

}
