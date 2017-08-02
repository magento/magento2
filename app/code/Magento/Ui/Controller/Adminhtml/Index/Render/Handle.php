<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index\Render;

use Magento\Framework\View\Element\Template;
use Magento\Ui\Component\Control\ActionPool;
use Magento\Ui\Component\Wrapper\UiComponent;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

/**
 * Class Handle
 * @since 2.1.0
 */
class Handle extends AbstractAction
{
    /**
     * Render UI component by namespace in handle context
     *
     * @return void
     * @since 2.1.0
     */
    public function execute()
    {
        $handle = $this->_request->getParam('handle');
        $namespace = $this->_request->getParam('namespace');
        $buttons = $this->_request->getParam('buttons', false);

        $this->_view->loadLayout(['default', $handle], true, true, false);

        $uiComponent = $this->_view->getLayout()->getBlock($namespace);
        $response = $uiComponent instanceof UiComponent ? $uiComponent->toHtml() : '';

        if ($buttons) {
            $actionsToolbar = $this->_view->getLayout()->getBlock(ActionPool::ACTIONS_PAGE_TOOLBAR);
            $response .= $actionsToolbar instanceof Template ? $actionsToolbar->toHtml() : '';
        }

        $this->_response->appendBody($response);
    }
}
