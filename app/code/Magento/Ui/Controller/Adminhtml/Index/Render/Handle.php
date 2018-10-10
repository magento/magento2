<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index\Render;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Element\Template;
use Magento\Ui\Component\Control\ActionPool;
use Magento\Ui\Component\Wrapper\UiComponent;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

/**
 * Class Handle
 */
class Handle extends AbstractAction implements HttpGetActionInterface
{
    /**
     * Render UI component by namespace in handle context
     *
     * @return void
     */
    public function execute()
    {
        $response = '';
        $handle = $this->_request->getParam('handle');
        $namespace = $this->_request->getParam('namespace');
        $buttons = $this->_request->getParam('buttons', false);

        $component = $this->factory->create($namespace);
        if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
            $this->_view->loadLayout(['default', $handle], true, true, false);

            $uiComponent = $this->_view->getLayout()->getBlock($namespace);
            $response = $uiComponent instanceof UiComponent ? $uiComponent->toHtml() : '';
        }

        if ($buttons) {
            $actionsToolbar = $this->_view->getLayout()->getBlock(ActionPool::ACTIONS_PAGE_TOOLBAR);
            $response .= $actionsToolbar instanceof Template ? $actionsToolbar->toHtml() : '';
        }

        $this->_response->appendBody($response);
    }

    /**
     * Optionally validate ACL resource of components with a DataSource/DataProvider
     *
     * @param mixed $dataProviderConfigData
     * @return bool
     */
    private function validateAclResource($dataProviderConfigData)
    {
        if (isset($dataProviderConfigData['aclResource'])
            && !$this->_authorization->isAllowed($dataProviderConfigData['aclResource'])
        ) {
            if (!$this->_request->isAjax()) {
                $this->_redirect('admin/denied');
            }
            return false;
        }

        return true;
    }
}
