<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Adminhtml account controller
 */
namespace Magento\MediaStorage\Controller\Adminhtml\System\Config\System;

abstract class Storage extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::system';

    /**
     * Return file storage singleton
     *
     * @return \Magento\MediaStorage\Model\File\Storage
     */
    protected function _getSyncSingleton()
    {
        return $this->_objectManager->get(\Magento\MediaStorage\Model\File\Storage::class);
    }

    /**
     * Return synchronize process status flag
     *
     * @return \Magento\MediaStorage\Model\File\Storage\Flag
     */
    protected function _getSyncFlag()
    {
        return $this->_getSyncSingleton()->getSyncFlag();
    }
}
