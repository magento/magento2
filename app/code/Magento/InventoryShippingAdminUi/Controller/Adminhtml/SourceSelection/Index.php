<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Controller\Adminhtml\SourceSelection;

use Magento\Backend\App\Action;

/**
 * Class Index
 */
class Index extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_InventoryApi::source';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Sales::sales_order');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Source Selection'));
        $this->_view->renderLayout();
    }
}
