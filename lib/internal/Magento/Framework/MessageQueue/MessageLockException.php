<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class MessageLockException to be thrown when a message being processed is already in the lock table.
 *
 * @api
 * @since 103.0.0
 */
class MessageLockException extends LocalizedException
{

}
