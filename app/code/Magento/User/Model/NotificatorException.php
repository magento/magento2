<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\User\Model;

use Magento\Framework\Exception\MailException;
use Magento\User\Model\Spi\NotificationExceptionInterface;

/**
 * When notificator cannot send an email.
 */
class NotificatorException extends MailException implements NotificationExceptionInterface
{

}
