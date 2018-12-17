<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System;

use Magento\AdminNotification\Model;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Notification\MessageInterface;

/**
 * Class Message
 *
 * @package Magento\AdminNotification\Model\System
 * @codeCoverageIgnore
 * @api
 * @since 100.0.2
 */
class Message extends AbstractModel implements MessageInterface
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct(): void //phpcs:ignore
    {
        $this->_init(Model\ResourceModel\System\Message::class);
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed(): bool
    {
        return true;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText(): string
    {
        return $this->getData('text');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity(): int
    {
        return $this->_getData('severity');
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->_getData('identity');
    }
}
