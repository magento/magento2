<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Test\Unit\Model\Checkout\Plugin;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

class ValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementsValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $repositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->agreementsValidatorMock = $this->createMock(\Magento\Checkout\Api\AgreementsValidatorInterface::class);
        $this->subjectMock = $this->createMock(\Magento\Checkout\Api\PaymentInformationManagementInterface::class);
        $this->paymentMock = $this->createMock(\Magento\Quote\Api\Data\PaymentInterface::class);
        $this->addressMock = $this->createMock(\Magento\Quote\Api\Data\AddressInterface::class);
        $this->extensionAttributesMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\PaymentExtension::class,
            ['getAgreementIds']
        );
        $this->scopeConfigMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->repositoryMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface::class
        );

        $this->model = new \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation(
            $this->agreementsValidatorMock,
            $this->scopeConfigMock,
            $this->repositoryMock
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrder()
    {
        $cartId = 100;
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->repositoryMock->expects($this->once())->method('getList')->willReturn([1]);
        $this->extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);
        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(true);
        $this->paymentMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->model->beforeSavePaymentInformation($this->subjectMock, $cartId, $this->paymentMock, $this->addressMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Please agree to all the terms and conditions before placing the order.
     */
    public function testBeforeSavePaymentInformationAndPlaceOrderIfAgreementsNotValid()
    {
        $cartId = 100;
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->repositoryMock->expects($this->once())->method('getList')->willReturn([1]);
        $this->extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);
        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(false);
        $this->paymentMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->model->beforeSavePaymentInformation($this->subjectMock, $cartId, $this->paymentMock, $this->addressMock);
    }

    public function testBeforeSavePaymentInformation()
    {
        $cartId = 100;
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->repositoryMock->expects($this->once())->method('getList')->willReturn([1]);
        $this->extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);
        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(true);
        $this->paymentMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->model->beforeSavePaymentInformation($this->subjectMock, $cartId, $this->paymentMock, $this->addressMock);
    }
}
