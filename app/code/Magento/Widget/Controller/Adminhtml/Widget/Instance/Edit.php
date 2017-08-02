<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

/**
 * Class \Magento\Widget\Controller\Adminhtml\Widget\Instance\Edit
 *
 * @since 2.0.0
 */
class Edit extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Edit widget instance action
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

        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $widgetInstance->getId() ? $widgetInstance->getTitle() : __('New Widget')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Widgets'));
        $this->_view->renderLayout();
    }
}
