<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Delete extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Delete Action
     *
     * @return void
     */
    public function execute()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if ($widgetInstance) {
            try {
                $widgetInstance->delete();
                $this->messageManager->addSuccessMessage(__('The widget instance has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/*/');
        return;
    }
}
