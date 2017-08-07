<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Controller\Payflow;

/**
 * Class \Magento\Paypal\Controller\Payflow\SilentPost
 *
 */
class SilentPost extends \Magento\Paypal\Controller\Payflow
{
    /**
     * Get response from PayPal by silent post method
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (isset($data['INVNUM'])) {
            /** @var $paymentModel \Magento\Paypal\Model\Payflowlink */
            $paymentModel = $this->_payflowModelFactory->create();
            try {
                $paymentModel->process($data);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
            }
        }
    }
}
