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
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextFactory;
use Magento\Framework\App\ObjectManager;

/**
 * Class Handle
 */
class Handle extends AbstractAction
{
    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param ContextFactory|null $contextFactory
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        ContextFactory $contextFactory = null
    ) {
        parent::__construct($context, $factory);
        $this->contextFactory = $contextFactory
            ?: ObjectManager::getInstance()->get(ContextFactory::class);
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
        $context = $this->contextFactory->create(
            [
                'namespace' => $namespace,
                'pageLayout' => $layout,
            ]
        );

        $component = $this->factory->create($namespace, null, ['context' => $context]);
        if ($this->validateAclResource($component->getContext()->getDataProvider()->getConfigData())) {
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
     * Optionally validate ACL resource of components with a DataSource/DataProvider
     *
     * @param mixed $dataProviderConfigData
     * @return bool
     */
    private function validateAclResource($dataProviderConfigData): bool
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
