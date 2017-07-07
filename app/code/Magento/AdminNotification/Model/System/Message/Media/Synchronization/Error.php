<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminNotification\Model\System\Message\Media\Synchronization;

/**
 * @api
 */
class Error extends \Magento\AdminNotification\Model\System\Message\Media\AbstractSynchronization
{
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity = 'MEDIA_SYNCHRONIZATION_ERROR';

    /**
     * Check whether
     *
     * @return bool
     */
    protected function _shouldBeDisplayed()
    {
        $data = $this->_syncFlag->getFlagData();
        return isset($data['has_errors']) && true == $data['has_errors'];
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __(
            'We were unable to synchronize one or more media files. Please refer to the log file for details.'
        );
    }
}
