<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Locked administrators controller
 */
namespace Magento\User\Controller\Adminhtml;

/**
 * Class \Magento\User\Controller\Adminhtml\Locks
 *
 * @since 2.0.0
 */
abstract class Locks extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_User::locks';
}
