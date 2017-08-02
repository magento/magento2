<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Save
 *
 * @since 2.0.0
 */
class Save extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Save action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if (!$widgetInstance) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        $widgetInstance->setTitle(
            $this->getRequest()->getPost('title')
        )->setStoreIds(
            $this->getRequest()->getPost('store_ids', [0])
        )->setSortOrder(
            $this->getRequest()->getPost('sort_order', 0)
        )->setPageGroups(
            $this->getRequest()->getPost('widget_instance')
        )->setWidgetParameters(
            $this->getRequest()->getPost('parameters')
        );
        try {
            $widgetInstance->save();
            $this->messageManager->addSuccess(__('The widget instance has been saved.'));
            if ($this->getRequest()->getParam('back', false)) {
                $this->_redirect(
                    'adminhtml/*/edit',
                    ['instance_id' => $widgetInstance->getId(), '_current' => true]
                );
            } else {
                $this->_redirect('adminhtml/*/');
            }
            return;
        } catch (\Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
            $this->_logger->critical($exception);
            $this->_redirect('adminhtml/*/edit', ['_current' => true]);
            return;
        }
        $this->_redirect('adminhtml/*/');
        return;
    }
}
