<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdminNotification\Model\System\Message\Media\Synchronization;

/**
 * @api
 */
class Success extends \Magento\AdminNotification\Model\System\Message\Media\AbstractSynchronization
{
    /**
     * Message identity
     *
     * @var string
     */
    protected $_identity = 'MEDIA_SYNCHRONIZATION_SUCCESS';

    /**
     * Check whether
     *
     * @return bool
     */
    protected function _shouldBeDisplayed()
    {
        $state = $this->_syncFlag->getState();
        $data = $this->_syncFlag->getFlagData();
        $hasErrors = isset($data['has_errors']) && true == $data['has_errors'] ? true : false;
        return false == $hasErrors && \Magento\MediaStorage\Model\File\Storage\Flag::STATE_FINISHED == $state;
    }

    /**
     * Retrieve message text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getText()
    {
        return __('Synchronization of media storages has been completed.');
    }
}
