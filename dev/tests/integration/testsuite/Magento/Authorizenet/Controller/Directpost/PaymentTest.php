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
}
