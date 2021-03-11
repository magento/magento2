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
        $this->assertStringContainsString(
            'authorizenet/directpost_payment/redirect/success/0/error_msg/The%20transaction%20was'
                . '%20declined%20because%20the%20response%20hash%20validation%20failed.',
            // @codingStandardsIgnoreEnd
            $this->getResponse()->getBody()
        );
    }
}
