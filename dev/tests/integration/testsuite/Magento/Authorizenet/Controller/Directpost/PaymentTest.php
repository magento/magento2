<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost;

/**
 * Class PaymentTest
 */
class PaymentTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testResponseActionValidationFailed()
    {
        $this->getRequest()->setPostValue('controller_action_name', 'onepage');
        $this->dispatch('authorizenet/directpost_payment/response');
        // @codingStandardsIgnoreStart
        $this->assertContains(
            'authorizenet/directpost_payment/redirect/success/0/error_msg/The%20transaction%20was'
                . '%20declined%20because%20the%20response%20hash%20validation%20failed.',
            // @codingStandardsIgnoreEnd
            $this->getResponse()->getBody()
        );
    }

    public function testBackendResponseActionOrderSuccess()
    {
        $xNum = 1;
        $this->getRequest()->setPostValue('x_invoice_num', $xNum);
        $this->dispatch('authorizenet/directpost_payment/backendresponse');
        $this->assertContains(
            '/sales/order/view/',
            $this->getResponse()->getBody()
        );
    }

    public function testBackendResponseActionValidationFailed()
    {
        $this->getRequest()->setPostValue('controller_action_name', 'action_name');
        $this->dispatch('authorizenet/directpost_payment/backendresponse');
        // @codingStandardsIgnoreStart
        $this->assertContains(
            'authorizenet_directpost_payment/redirect/success/0/error_msg/The%20transaction%20was%20declined'
                . '%20because%20the%20response%20hash%20validation%20failed./controller_action_name/action_name/',
            // @codingStandardsIgnoreEnd
            $this->getResponse()->getBody()
        );
    }
}
