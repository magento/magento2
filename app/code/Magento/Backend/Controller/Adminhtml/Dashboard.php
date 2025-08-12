<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Dashboard admin controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Controller\Adminhtml;

abstract class Dashboard extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::dashboard';
}
