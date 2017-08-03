<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

/**
 * Class \Magento\Paypal\Controller\Payflow\CancelPayment
 *
 * @since 2.0.0
 */
class CancelPayment extends \Magento\Paypal\Controller\Payflow
{
    /**
     * When a customer cancel payment from payflow gateway.
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_view->loadLayout(false);
        $gotoSection = $this->_cancelPayment();
        $redirectBlock = $this->_view->getLayout()->getBlock($this->_redirectBlockName);
        $redirectBlock->setGotoSection($gotoSection);
        $this->_view->renderLayout();
    }
}
