<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Dashboard admin controller
 */
namespace Magento\Backend\Controller\Adminhtml;

abstract class Dashboard extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Backend::dashboard';
}
