<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Delete
 *
 * @since 2.0.0
 */
class Delete extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Delete Action
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $widgetInstance = $this->_initWidgetInstance();
        if ($widgetInstance) {
            try {
                $widgetInstance->delete();
                $this->messageManager->addSuccess(__('The widget instance has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/*/');
        return;
    }
}
