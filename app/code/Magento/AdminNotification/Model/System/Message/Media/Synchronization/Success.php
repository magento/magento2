<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminNotification\Model\System\Message\Media\Synchronization;

use Magento\AdminNotification\Model\System\Message\Media\AbstractSynchronization;
use Magento\Framework\Phrase;
use Magento\MediaStorage\Model\File\Storage\Flag;

/**
 * Class Success
 *
 * @package Magento\AdminNotification\Model\System\Message\Media\Synchronization
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Success extends AbstractSynchronization
{
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity = 'MEDIA_SYNCHRONIZATION_SUCCESS'; //phpcs:ignore

    /**
     * Check whether
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _shouldBeDisplayed(): bool //phpcs:ignore
    {
        $state = $this->_syncFlag->getState();
        $data = $this->_syncFlag->getFlagData();
        $hasErrors = isset($data['has_errors']) && true == $data['has_errors'] ? true : false;
        return false == $hasErrors && Flag::STATE_FINISHED == $state;
    }

    /**
     * Retrieve message text
     *
     * @return Phrase
     */
    public function getText(): Phrase
    {
        return __('Synchronization of media storages has been completed.');
    }
}
