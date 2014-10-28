<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Service\V1\Data;

/**
 * Class OrderPaymentTest
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @package Magento\Sales\Service\V1\Data
 */
class OrderPaymentTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAccountStatus()
    {
        $data = ['account_status' => 'test_value_account_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_account_status', $object->getAccountStatus());
    }

    public function testGetAdditionalData()
    {
        $data = ['additional_data' => 'test_value_additional_data'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_additional_data', $object->getAdditionalData());
    }

    public function testGetAdditionalInformation()
    {
        $data = ['additional_information' => 'test_value_additional_information'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_additional_information', $object->getAdditionalInformation());
    }

    public function testGetAddressStatus()
    {
        $data = ['address_status' => 'test_value_address_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_address_status', $object->getAddressStatus());
    }

    public function testGetAmountAuthorized()
    {
        $data = ['amount_authorized' => 'test_value_amount_authorized'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_amount_authorized', $object->getAmountAuthorized());
    }

    public function testGetAmountCanceled()
    {
        $data = ['amount_canceled' => 'test_value_amount_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_amount_canceled', $object->getAmountCanceled());
    }

    public function testGetAmountOrdered()
    {
        $data = ['amount_ordered' => 'test_value_amount_ordered'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_amount_ordered', $object->getAmountOrdered());
    }

    public function testGetAmountPaid()
    {
        $data = ['amount_paid' => 'test_value_amount_paid'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_amount_paid', $object->getAmountPaid());
    }

    public function testGetAmountRefunded()
    {
        $data = ['amount_refunded' => 'test_value_amount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_amount_refunded', $object->getAmountRefunded());
    }

    public function testGetAnetTransMethod()
    {
        $data = ['anet_trans_method' => 'test_value_anet_trans_method'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_anet_trans_method', $object->getAnetTransMethod());
    }

    public function testGetBaseAmountAuthorized()
    {
        $data = ['base_amount_authorized' => 'test_value_base_amount_authorized'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_authorized', $object->getBaseAmountAuthorized());
    }

    public function testGetBaseAmountCanceled()
    {
        $data = ['base_amount_canceled' => 'test_value_base_amount_canceled'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_canceled', $object->getBaseAmountCanceled());
    }

    public function testGetBaseAmountOrdered()
    {
        $data = ['base_amount_ordered' => 'test_value_base_amount_ordered'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_ordered', $object->getBaseAmountOrdered());
    }

    public function testGetBaseAmountPaid()
    {
        $data = ['base_amount_paid' => 'test_value_base_amount_paid'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_paid', $object->getBaseAmountPaid());
    }

    public function testGetBaseAmountPaidOnline()
    {
        $data = ['base_amount_paid_online' => 'test_value_base_amount_paid_online'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_paid_online', $object->getBaseAmountPaidOnline());
    }

    public function testGetBaseAmountRefunded()
    {
        $data = ['base_amount_refunded' => 'test_value_base_amount_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_refunded', $object->getBaseAmountRefunded());
    }

    public function testGetBaseAmountRefundedOnline()
    {
        $data = ['base_amount_refunded_online' => 'test_value_base_amount_refunded_online'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_amount_refunded_online', $object->getBaseAmountRefundedOnline());
    }

    public function testGetBaseShippingAmount()
    {
        $data = ['base_shipping_amount' => 'test_value_base_shipping_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_amount', $object->getBaseShippingAmount());
    }

    public function testGetBaseShippingCaptured()
    {
        $data = ['base_shipping_captured' => 'test_value_base_shipping_captured'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_captured', $object->getBaseShippingCaptured());
    }

    public function testGetBaseShippingRefunded()
    {
        $data = ['base_shipping_refunded' => 'test_value_base_shipping_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_base_shipping_refunded', $object->getBaseShippingRefunded());
    }

    public function testGetCcApproval()
    {
        $data = ['cc_approval' => 'test_value_cc_approval'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_approval', $object->getCcApproval());
    }

    public function testGetCcAvsStatus()
    {
        $data = ['cc_avs_status' => 'test_value_cc_avs_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_avs_status', $object->getCcAvsStatus());
    }

    public function testGetCcCidStatus()
    {
        $data = ['cc_cid_status' => 'test_value_cc_cid_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_cid_status', $object->getCcCidStatus());
    }

    public function testGetCcDebugRequestBody()
    {
        $data = ['cc_debug_request_body' => 'test_value_cc_debug_request_body'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_debug_request_body', $object->getCcDebugRequestBody());
    }

    public function testGetCcDebugResponseBody()
    {
        $data = ['cc_debug_response_body' => 'test_value_cc_debug_response_body'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_debug_response_body', $object->getCcDebugResponseBody());
    }

    public function testGetCcDebugResponseSerialized()
    {
        $data = ['cc_debug_response_serialized' => 'test_value_cc_debug_response_serialized'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_debug_response_serialized', $object->getCcDebugResponseSerialized());
    }

    public function testGetCcExpMonth()
    {
        $data = ['cc_exp_month' => 'test_value_cc_exp_month'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_exp_month', $object->getCcExpMonth());
    }

    public function testGetCcExpYear()
    {
        $data = ['cc_exp_year' => 'test_value_cc_exp_year'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_exp_year', $object->getCcExpYear());
    }

    public function testGetCcLast4()
    {
        $data = ['cc_last4' => 'test_value_cc_last4'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_last4', $object->getCcLast4());
    }

    public function testGetCcNumberEnc()
    {
        $data = ['cc_number_enc' => 'test_value_cc_number_enc'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_number_enc', $object->getCcNumberEnc());
    }

    public function testGetCcOwner()
    {
        $data = ['cc_owner' => 'test_value_cc_owner'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_owner', $object->getCcOwner());
    }

    public function testGetCcSecureVerify()
    {
        $data = ['cc_secure_verify' => 'test_value_cc_secure_verify'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_secure_verify', $object->getCcSecureVerify());
    }

    public function testGetCcSsIssue()
    {
        $data = ['cc_ss_issue' => 'test_value_cc_ss_issue'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_ss_issue', $object->getCcSsIssue());
    }

    public function testGetCcSsStartMonth()
    {
        $data = ['cc_ss_start_month' => 'test_value_cc_ss_start_month'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_ss_start_month', $object->getCcSsStartMonth());
    }

    public function testGetCcSsStartYear()
    {
        $data = ['cc_ss_start_year' => 'test_value_cc_ss_start_year'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_ss_start_year', $object->getCcSsStartYear());
    }

    public function testGetCcStatus()
    {
        $data = ['cc_status' => 'test_value_cc_status'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_status', $object->getCcStatus());
    }

    public function testGetCcStatusDescription()
    {
        $data = ['cc_status_description' => 'test_value_cc_status_description'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_status_description', $object->getCcStatusDescription());
    }

    public function testGetCcTransId()
    {
        $data = ['cc_trans_id' => 'test_value_cc_trans_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_trans_id', $object->getCcTransId());
    }

    public function testGetCcType()
    {
        $data = ['cc_type' => 'test_value_cc_type'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_cc_type', $object->getCcType());
    }

    public function testGetEcheckAccountName()
    {
        $data = ['echeck_account_name' => 'test_value_echeck_account_name'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_echeck_account_name', $object->getEcheckAccountName());
    }

    public function testGetEcheckAccountType()
    {
        $data = ['echeck_account_type' => 'test_value_echeck_account_type'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_echeck_account_type', $object->getEcheckAccountType());
    }

    public function testGetEcheckBankName()
    {
        $data = ['echeck_bank_name' => 'test_value_echeck_bank_name'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_echeck_bank_name', $object->getEcheckBankName());
    }

    public function testGetEcheckRoutingNumber()
    {
        $data = ['echeck_routing_number' => 'test_value_echeck_routing_number'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_echeck_routing_number', $object->getEcheckRoutingNumber());
    }

    public function testGetEcheckType()
    {
        $data = ['echeck_type' => 'test_value_echeck_type'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_echeck_type', $object->getEcheckType());
    }

    public function testGetEntityId()
    {
        $data = ['entity_id' => 'test_value_entity_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_entity_id', $object->getEntityId());
    }

    public function testGetLastTransId()
    {
        $data = ['last_trans_id' => 'test_value_last_trans_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_last_trans_id', $object->getLastTransId());
    }

    public function testGetMethod()
    {
        $data = ['method' => 'test_value_method'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_method', $object->getMethod());
    }

    public function testGetParentId()
    {
        $data = ['parent_id' => 'test_value_parent_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_parent_id', $object->getParentId());
    }

    public function testGetPoNumber()
    {
        $data = ['po_number' => 'test_value_po_number'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_po_number', $object->getPoNumber());
    }

    public function testGetProtectionEligibility()
    {
        $data = ['protection_eligibility' => 'test_value_protection_eligibility'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_protection_eligibility', $object->getProtectionEligibility());
    }

    public function testGetQuotePaymentId()
    {
        $data = ['quote_payment_id' => 'test_value_quote_payment_id'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_quote_payment_id', $object->getQuotePaymentId());
    }

    public function testGetShippingAmount()
    {
        $data = ['shipping_amount' => 'test_value_shipping_amount'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_amount', $object->getShippingAmount());
    }

    public function testGetShippingCaptured()
    {
        $data = ['shipping_captured' => 'test_value_shipping_captured'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_captured', $object->getShippingCaptured());
    }

    public function testGetShippingRefunded()
    {
        $data = ['shipping_refunded' => 'test_value_shipping_refunded'];
        $abstractBuilderMock = $this->getMockBuilder('Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder')
            ->setMethods(['getData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $abstractBuilderMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $object = new \Magento\Sales\Service\V1\Data\OrderPayment($abstractBuilderMock);

        $this->assertEquals('test_value_shipping_refunded', $object->getShippingRefunded());
    }
}
