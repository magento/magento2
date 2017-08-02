<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\EncryptionKey\Controller\Adminhtml\Crypt;

/**
 * Encryption key changer controller
 * @since 2.0.0
 */
abstract class Key extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_EncryptionKey::crypt_key';
}
