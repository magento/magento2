<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Controller\Index;

class NoCookies extends \Magento\Framework\App\Action\Action
{
    /**
     * No cookies action
     *
     * @return void
     */
    public function execute()
    {
        $redirect = new \Magento\Framework\DataObject();
        $this->_eventManager->dispatch(
            'controller_action_nocookies',
            ['action' => $this, 'redirect' => $redirect]
        );

        $url = $redirect->getRedirectUrl();
        if ($url) {
            $this->getResponse()->setRedirect($url);
        } elseif ($redirect->getRedirect()) {
            $this->_redirect($redirect->getPath(), $redirect->getArguments());
        } else {
            $this->_view->loadLayout(['default', 'noCookie']);
            $this->_view->renderLayout();
        }

        $this->getRequest()->setDispatched(true);
    }
}
