<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System\Message\Media;

use Magento\Framework\Notification\MessageInterface;
use Magento\MediaStorage\Model\File\Storage\Flag;

/**
 * Class AbstractSynchronization
 *
 * @package Magento\AdminNotification\Model\System\Message\Media
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
abstract class AbstractSynchronization implements MessageInterface
{
    /**
     * @var Flag
     */
    protected $_syncFlag; //phpcs:ignore

    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity; //phpcs:ignore

    /**
     * Is displayed flag
     *
     * @var bool
     */
    protected $_isDisplayed = null; //phpcs:ignore

    /**
     * @param Flag $fileStorage
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(Flag $fileStorage)
    {
        $this->_syncFlag = $fileStorage->loadSelf();
    }

    /**
     * Check if message should be displayed
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    abstract protected function _shouldBeDisplayed(); //phpcs:ignore

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->_identity;
    }

    /**
     * Check whether
     *
     * @return bool
     * @throws \Exception
     */
    public function isDisplayed(): bool
    {
        if (null === $this->_isDisplayed) {
            $output = $this->_shouldBeDisplayed();
            if ($output) {
                $this->_syncFlag->setState(Flag::STATE_NOTIFIED);
                $this->_syncFlag->save();
            }
            $this->_isDisplayed = $output;
        }
        return $this->_isDisplayed;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity(): int
    {
        return MessageInterface::SEVERITY_MAJOR;
    }
}
