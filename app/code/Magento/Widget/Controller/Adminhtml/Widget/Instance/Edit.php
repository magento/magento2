<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Edit extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Edit widget instance action
     *
     * @return void
     */
    public function execute(): void
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
