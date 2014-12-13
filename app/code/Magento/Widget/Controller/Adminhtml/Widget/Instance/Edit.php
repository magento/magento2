<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Edit extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Edit widget instance action
     *
     * @return void
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
            $widgetInstance->getId() ? $widgetInstance->getTitle() : __('New Frontend App Instance')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Frontend Apps'));
        $this->_view->renderLayout();
    }
}
