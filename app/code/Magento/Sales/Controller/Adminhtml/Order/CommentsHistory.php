<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Adminhtml\Order;


class CommentsHistory extends \Magento\Sales\Controller\Adminhtml\Order
{
    /**
     * Generate order history for ajax request
     *
     * @return void
     */
    public function execute()
    {
        $this->_initOrder();

        $html = $this->_view->getLayout()->createBlock(
            'Magento\Sales\Block\Adminhtml\Order\View\Tab\History'
        )->toHtml();

        $this->_translateInline->processResponseBody($html);

        $this->getResponse()->setBody($html);
    }
}
