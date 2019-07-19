<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Controller\Directpost\Payment;

/**
 * DirectPost payment response controller.
 * @deprecated 2.2.9 Authorize.net is removing all support for this payment method
 */
class Response extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_responseAction('frontend');
        return $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
    }
}
