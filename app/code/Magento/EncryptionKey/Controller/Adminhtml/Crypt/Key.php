<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt;

/**
 * Encryption key changer controller
 */
abstract class Key extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_EncryptionKey::crypt_key';
}
