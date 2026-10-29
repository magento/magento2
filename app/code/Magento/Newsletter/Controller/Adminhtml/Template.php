<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
/**
 * Manage Newsletter Template Controller
 */
namespace Magento\Newsletter\Controller\Adminhtml;

abstract class Template extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Newsletter::template';
}
