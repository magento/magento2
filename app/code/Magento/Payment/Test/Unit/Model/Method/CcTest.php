<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Method\Cc;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Payment;

class CcTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Cc
     */
    private $ccModel;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->ccModel = $objectManager->getObject(Cc::class);
    }
    
    public function testAssignData()
    {
        $additionalData = [
            'cc_type' => 'VI',
            'cc_owner' => 'Bruce',
            'cc_number' => '41111111111111',
            'cc_cid' => '42',
            'cc_exp_month' => '02',
            'cc_exp_year' => '30',
            'cc_ss_issue' => '9',
            'cc_ss_start_month' => '01',
            'cc_ss_start_year' => '30'
        ];

        $inputData = new DataObject(
            [
                PaymentInterface::KEY_ADDITIONAL_DATA => $additionalData
            ]
        );

        $payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedData = [
            'cc_type' => 'VI',
            'cc_owner' => 'Bruce',
            'cc_last_4' => '1111',
            'cc_number' => '41111111111111',
            'cc_cid' => '42',
            'cc_exp_month' => '02',
            'cc_exp_year' => '30',
            'cc_ss_issue' => '9',
            'cc_ss_start_month' => '01',
            'cc_ss_start_year' => '30'
        ];

        $payment->expects(static::once())
            ->method('addData')
            ->with(
                $expectedData
            );

        $this->ccModel->setInfoInstance($payment);
        $this->ccModel->assignData($inputData);
    }
}
