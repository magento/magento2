<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;

/**
 * Adminhtml account controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class Account extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::myaccount';
}
