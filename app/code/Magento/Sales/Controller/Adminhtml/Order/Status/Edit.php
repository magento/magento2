<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Controller\Adminhtml\Order\Status;

class Edit extends \Magento\Sales\Controller\Adminhtml\Order\Status
{
    /**
     * Editing existing status form
     *
     * @return void
     */
    public function execute()
    {
        $status = $this->_initStatus();
        if ($status) {
            $this->_coreRegistry->register('current_status', $status);
            $this->_title->add(__('Order Status'));
            $this->_title->add(__('Edit Order Status'));
            $this->_view->loadLayout();
            $this->_setActiveMenu('Magento_Sales::system_order_statuses');
            $this->_view->renderLayout();
        } else {
            $this->messageManager->addError(__('We can\'t find this order status.'));
            $this->_redirect('sales/');
        }
    }
}
