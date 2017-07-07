<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Adminhtml\Paypal\Reports;

class Details extends \Magento\Paypal\Controller\Adminhtml\Paypal\Reports
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Paypal::paypal_settlement_reports_view';

    /**
     * View transaction details action
     *
     * @return void
     */
    public function execute()
    {
        $rowId = $this->getRequest()->getParam('id');
        $row = $this->_rowFactory->create()->load($rowId);
        if (!$row->getId()) {
            $this->_redirect('adminhtml/*/');
            return;
        }
        $this->_coreRegistry->register('current_transaction', $row);
        $this->_initAction();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('View Transaction'));
        $this->_addContent(
            $this->_view->getLayout()->createBlock(
                \Magento\Paypal\Block\Adminhtml\Settlement\Details::class,
                'settlementDetails'
            )
        );
        $this->_view->renderLayout();
    }
}
