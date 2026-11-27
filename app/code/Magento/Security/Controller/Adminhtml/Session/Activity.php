<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Security\Controller\Adminhtml\Session;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Admin session activity
 */
class Activity extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Magento_Backend::admin';

    /**
     * Render the admin sessions activity page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Account Activity'));
        $this->_view->renderLayout();
    }
}
