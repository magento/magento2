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
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponent\Config\ManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class Handle
 */
class Handle extends AbstractAction
{
    /**
     * @var ManagerInterface
     */
    private $componentManager;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param ManagerInterface|null $componentManager
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        ManagerInterface $componentManager = null
    ) {
        parent::__construct($context, $factory);
        $this->componentManager = $componentManager ?: ObjectManager::getInstance()->get(ManagerInterface::class);
    }

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
        $this->_view->loadLayout(['default', $handle], true, true, false);
        $layout = $this->_view->getLayout();
        $config = $this->componentManager->getData($namespace);

        if ($this->validateAclResource($config[$namespace])) {
            $uiComponent = $layout->getBlock($namespace);
            $response = $uiComponent instanceof UiComponent ? $uiComponent->toHtml() : '';
        }

        if ($buttons) {
            $actionsToolbar = $layout->getBlock(ActionPool::ACTIONS_PAGE_TOOLBAR);
            $response .= $actionsToolbar instanceof Template ? $actionsToolbar->toHtml() : '';
        }

        $this->_response->appendBody($response);
    }

    /**
     * Optionally validate ACL resource of components.
     *
     * @param mixed $dataProviderConfigData
     * @return bool
     */
    private function validateAclResource($dataProviderConfigData)
    {
        $aclResource = isset($dataProviderConfigData['arguments']['data']['acl'])
            ? $dataProviderConfigData['arguments']['data']['acl']
            : false;
        if ($aclResource !== false && !$this->_authorization->isAllowed($aclResource)) {
            if (!$this->_request->isAjax()) {
                $this->_redirect('admin/denied');
            }

            return false;
        }

        return true;
    }
}
