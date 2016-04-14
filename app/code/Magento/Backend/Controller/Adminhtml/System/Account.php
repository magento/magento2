<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System;

use Magento\Backend\App\Action;

/**
 * Adminhtml account controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
