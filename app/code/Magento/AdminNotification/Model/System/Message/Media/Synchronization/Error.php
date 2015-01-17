<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System\Message\Media\Synchronization;

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
     * @return string
     */
    public function getText()
    {
        return __(
            'One or more media files failed to be synchronized during the media storages synchronization process. Refer to the log file for details.'
        );
    }
}
