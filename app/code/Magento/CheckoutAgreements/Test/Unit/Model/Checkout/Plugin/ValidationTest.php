<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model\Checkout\Plugin;

class ValidationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementsValidatorMock;

    protected function setUp()
    {
        $this->agreementsValidatorMock = $this->getMock(
            '\Magento\CheckoutAgreements\Model\AgreementsValidator',
            [],
            [],
            '',
            false
        );
        $this->model = new \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation(
            $this->agreementsValidatorMock
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $agreements = [1, 2, 3];
        $subjectMock = $this->getMock('\Magento\Checkout\Api\PaymentInformationManagementInterface');
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $addressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $extensionAttributesMock = $this->getMock('\Magento\Quote\Api\Data\PaymentExtensionInterface');
        $extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);

        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(true);
        $paymentMock->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);

        $this->model->beforeSavePaymentInformation($subjectMock, $cartId, $paymentMock, $addressMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Please agree to all the terms and conditions before placing the order.
     */
    public function testBeforeSavePaymentInformationAndPlaceOrderIfAgreementsNotValid()
    {
        $cartId = 100;
        $agreements = [1, 2, 3];
        $subjectMock = $this->getMock('\Magento\Checkout\Api\PaymentInformationManagementInterface');
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $addressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $extensionAttributesMock = $this->getMock('\Magento\Quote\Api\Data\PaymentExtensionInterface');
        $extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);

        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(false);
        $paymentMock->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);

        $this->model->beforeSavePaymentInformation($subjectMock, $cartId, $paymentMock, $addressMock);
    }

    public function testBeforeSavePaymentInformation()
    {
        $cartId = 100;
        $agreements = [1, 2, 3];

        $extensionAttributesMock = $this->getMock('\Magento\Quote\Api\Data\PaymentExtensionInterface');
        $extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);

        $subjectMock = $this->getMock('\Magento\Checkout\Api\PaymentInformationManagementInterface');
        $paymentMock = $this->getMock('\Magento\Quote\Api\Data\PaymentInterface');
        $addressMock = $this->getMock('\Magento\Quote\Api\Data\AddressInterface');

        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(true);
        $paymentMock->expects($this->once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);

        $this->model->beforeSavePaymentInformation($subjectMock, $cartId, $paymentMock, $addressMock);
    }
}
