<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller\Noroute;

/**
 * Class \Magento\Framework\Controller\Noroute\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Noroute application handler
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $status = $this->getRequest()->getParam('__status__');
        if (!$status instanceof \Magento\Framework\DataObject) {
            $status = new \Magento\Framework\DataObject();
        }

        $this->_eventManager->dispatch('controller_action_noroute', ['action' => $this, 'status' => $status]);

        if ($status->getLoaded() !== true || $status->getForwarded() === true) {
            $this->_view->loadLayout(['default', 'noroute']);
            $this->_view->renderLayout();
        } else {
            $status->setForwarded(true);
            $request = $this->getRequest();
            $request->initForward();
            $request->setParams(['__status__' => $status]);
            $request->setControllerName($status->getForwardController());
            $request->setModuleName($status->getForwardModule());
            $request->setActionName($status->getForwardAction())->setDispatched(false);
        }
    }
}
