<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
            'authorizenet/directpost_payment/redirect/success/0/error_msg/The transaction was'
            . ' declined because the response hash validation failed.',
            // @codingStandardsIgnoreEnd
            $this->getResponse()->getBody()
        );
    }

    public function testRedirectActionErrorMessage()
    {
        $this->getRequest()->setParam('success', '0');
        $this->getRequest()->setParam('error_msg', 'Error message');
        $this->dispatch('authorizenet/directpost_payment/redirect');
        $this->assertContains('alert("Error message");', $this->getResponse()->getBody());
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
            'authorizenet_directpost_payment/redirect/success/0/error_msg/The transaction was declined'
            . ' because the response hash validation failed./controller_action_name/action_name/',
            // @codingStandardsIgnoreEnd
            $this->getResponse()->getBody()
        );
    }
}
