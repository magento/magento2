<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System\Message\Media\Synchronization;

use Magento\AdminNotification\Model\System\Message\Media\AbstractSynchronization;
use Magento\Framework\Phrase;

/**
 * Class Error
 *
 * @package Magento\AdminNotification\Model\System\Message\Media\Synchronization
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Error extends AbstractSynchronization
{
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity = 'MEDIA_SYNCHRONIZATION_ERROR'; //phpcs:ignore

    /**
     * Check whether
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _shouldBeDisplayed(): bool //phpcs:ignore
    {
        $data = $this->_syncFlag->getFlagData();
        return isset($data['has_errors']) && true == $data['has_errors'];
    }

    /**
     * Retrieve message text
     *
     * @return Phrase
     */
    public function getText(): Phrase
    {
        return __('We were unable to synchronize one or more media files. Please refer to the log file for details.');
    }
}
