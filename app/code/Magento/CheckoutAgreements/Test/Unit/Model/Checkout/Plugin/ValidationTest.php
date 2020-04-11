<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Unit\Model\Checkout\Plugin;

use PHPUnit\Framework\TestCase;
use Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\PaymentExtension;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;
use Magento\Framework\Api\SearchCriteria;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ValidationTest validates the agreement based on the payment method
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidationTest extends TestCase
{
    /**
     * @var Validation
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $agreementsValidatorMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var MockObject
     */
    protected $paymentMock;

    /**
     * @var MockObject
     */
    protected $addressMock;

    /**
     * @var MockObject
     */
    protected $extensionAttributesMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var MockObject
     */
    private $agreementsFilterMock;

    /**
     * @var MockObject
     */
    private $quoteMock;

    /**
     * @var MockObject
     */
    private $quoteRepositoryMock;

    protected function setUp(): void
    {
        $this->agreementsValidatorMock = $this->createMock(AgreementsValidatorInterface::class);
        $this->subjectMock = $this->createMock(PaymentInformationManagementInterface::class);
        $this->paymentMock = $this->createMock(PaymentInterface::class);
        $this->addressMock = $this->createMock(AddressInterface::class);
        $this->quoteMock = $this->createPartialMock(Quote::class, ['getIsMultiShipping']);
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->extensionAttributesMock = $this->createPartialMock(
            PaymentExtension::class,
            ['getAgreementIds']
        );
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->checkoutAgreementsListMock = $this->createMock(
            CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            ActiveStoreAgreementsFilter::class
        );

        $this->model = new Validation(
            $this->agreementsValidatorMock,
            $this->scopeConfigMock,
            $this->checkoutAgreementsListMock,
            $this->agreementsFilterMock,
            $this->quoteRepositoryMock
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
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->quoteMock
            ->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(false);
        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
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
        $this->model->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $this->paymentMock,
            $this->addressMock
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderIfAgreementsNotValid()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $cartId = 100;
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->quoteMock
            ->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(false);
        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
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
        $this->model->beforeSavePaymentInformation(
            $this->subjectMock,
            $cartId,
            $this->paymentMock,
            $this->addressMock
        );

        $this->expectExceptionMessage(
            "The order wasn't placed. First, agree to the terms and conditions, then try placing your order again."
        );
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
        $this->quoteMock
            ->expects($this->once())
            ->method('getIsMultiShipping')
            ->willReturn(false);
        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
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
        $this->model->beforeSavePaymentInformation($this->subjectMock, $cartId, $this->paymentMock, $this->addressMock);
    }
}
