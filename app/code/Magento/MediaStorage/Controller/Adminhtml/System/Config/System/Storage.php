<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Adminhtml account controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\MediaStorage\Controller\Adminhtml\System\Config\System;

abstract class Storage extends \Magento\Backend\App\Action
{
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
