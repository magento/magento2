<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Encryption key changer controller
 */
namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt;

abstract class Key extends \Magento\Backend\App\Action
{
    /**
     * Check whether current administrator session allows this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_EncryptionKey::crypt_key');
    }
}
