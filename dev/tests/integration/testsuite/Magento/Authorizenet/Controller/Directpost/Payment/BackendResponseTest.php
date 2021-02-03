<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\TestFramework\TestCase\AbstractController;

class BackendResponseTest extends AbstractController
{
    /**
     * @var string
     */
    private static $entryPoint = 'authorizenet/directpost_payment/backendresponse';

    /**
     * Checks a test case when request is processed from different to Authorize.net entry point.
     */
    public function testUnauthorizedRequest()
    {
        $data = [
            'x_response_code' => 1,
            'x_response_reason_code' => 1,
            'x_invoice_num' => '1',
            'x_amount' => 16,
            'x_trans_id' => '32iiw5ve',
            'x_card_type' => 'American Express',
            'x_account_number' => 'XXXX0002',
            'x_MD5_Hash' => 'h6a4s2h'
        ];
        $this->getRequest()->setPostValue($data);
        $this->dispatch(self::$entryPoint);

        self::assertEquals(302, $this->getResponse()->getHttpResponseCode());
        self::assertEmpty($this->getResponse()->getBody());
    }

    /**
     * Checks a test case when request is successfully processed.
     *
     * @magentoConfigFixture current_store payment/authorizenet_directpost/trans_md5 n4v2c5n0d
     * @magentoConfigFixture current_store payment/authorizenet_directpost/login merch1
     */
    public function testSuccess()
    {
        $data = [
            'x_response_code' => 1,
            'x_response_reason_code' => 1,
            'x_invoice_num' => '1',
            'x_amount' => 16,
            'x_trans_id' => '32iiw5ve',
            'x_card_type' => 'American Express',
            'x_account_number' => 'XXXX0002',
            'x_MD5_Hash' => '0EAD2F65D3D879CCB0D1A6F24883AC92'
        ];
        $this->getRequest()->setPostValue($data);
        $this->dispatch(self::$entryPoint);
        self::assertEquals(200, $this->getResponse()->getHttpResponseCode());
        self::assertStringContainsString('/sales/order/view', $this->getResponse()->getBody());
    }
}
