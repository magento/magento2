<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class MessageLockException to be thrown when a message being processed is already in the lock table.
 */
class MessageLockException extends LocalizedException
{

}
