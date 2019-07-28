<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
