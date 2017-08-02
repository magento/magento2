<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Controller\Index;

/**
 * Class \Magento\Cookie\Controller\Index\NoCookies
 *
 * @since 2.0.0
 */
class NoCookies extends \Magento\Framework\App\Action\Action
{
    /**
     * No cookies action
     *
     * @return void
     * @since 2.0.0
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
