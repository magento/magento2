<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Unit\Model\Checkout\Plugin;

use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $agreementsValidatorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $addressMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $extensionAttributesMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $agreementsFilterMock;

    protected function setUp(): void
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
        $this->checkoutAgreementsListMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            \Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter::class
        );

        $this->model = new \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation(
            $this->agreementsValidatorMock,
            $this->scopeConfigMock,
            $this->checkoutAgreementsListMock,
            $this->agreementsFilterMock
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
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([1]);
        $this->extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);
        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(true);
        $this->paymentMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->model->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $cartId,
            $this->paymentMock,
            $this->addressMock
        );
    }

    /**
     */
    public function testBeforeSavePaymentInformationAndPlaceOrderIfAgreementsNotValid()
    {
        $this->expectException(\Magento\Framework\Exception\CouldNotSaveException::class);

        $cartId = 100;
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->agreementsFilterMock->expects($this->once())
            ->method('buildSearchCriteria')
            ->willReturn($searchCriteriaMock);
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn([1]);
        $this->extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);
        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(false);
        $this->paymentMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->model->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $cartId,
            $this->paymentMock,
            $this->addressMock
        );

        $this->expectExceptionMessage(
            "The order wasn't placed. First, agree to the terms and conditions, then try placing your order again."
        );
    }
}
