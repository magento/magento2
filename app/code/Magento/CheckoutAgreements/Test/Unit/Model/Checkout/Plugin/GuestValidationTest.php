<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model\Checkout\Plugin;

use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;
use Magento\CheckoutAgreements\Model\Checkout\Plugin\GuestValidation;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentExtensionInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\GuestCartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GuestValidationTest extends TestCase
{
    /**
     * @var GuestValidation
     */
    private $model;

    /**
     * @var MockObject
     */
    private $agreementsValidatorMock;

    /**
     * @var MockObject
     */
    private $subjectMock;

    /**
     * @var MockObject
     */
    private $paymentMock;

    /**
     * @var MockObject
     */
    private $addressMock;

    /**
     * @var MockObject
     */
    private $extensionAttributesMock;

    /**
     * @var MockObject
     */
    private $scopeConfigMock;

    /**
     * @var MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var MockObject
     */
    private $agreementsFilterMock;

    /**
     * @var Quote|MockObject
     */
    private Quote|MockObject $quoteMock;

    /**
     * @var MaskedQuoteIdToQuoteId|MockObject
     */
    private MaskedQuoteIdToQuoteId|MockObject $maskedQuoteIdToQuoteIdMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private CartRepositoryInterface|MockObject $cartRepositoryMock;

    /**
     * @var Emulation|MockObject
     */
    private Emulation|MockObject $storeEmulationMock;

    protected function setUp(): void
    {
        $this->agreementsValidatorMock = $this->getMockForAbstractClass(AgreementsValidatorInterface::class);
        $this->subjectMock = $this->getMockForAbstractClass(GuestPaymentInformationManagementInterface::class);
        $this->paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->extensionAttributesMock = $this->getPaymentExtension();
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->checkoutAgreementsListMock = $this->createMock(
            CheckoutAgreementsListInterface::class
        );
        $this->agreementsFilterMock = $this->createMock(
            ActiveStoreAgreementsFilter::class
        );
        $this->quoteMock = $this->createMock(Quote::class);
        $this->maskedQuoteIdToQuoteIdMock = $this->createMock(MaskedQuoteIdToQuoteId::class);
        $this->cartRepositoryMock = $this->createMock(GuestCartRepositoryInterface::class);
        $this->storeEmulationMock = $this->createMock(Emulation::class);

        $storeId = 1;
        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('get')
            ->with('0CQwCntNHR4yN9P5PUAzbxatvDvBXOce')
            ->willReturn($this->quoteMock);

        $this->model = new GuestValidation(
            $this->agreementsValidatorMock,
            $this->scopeConfigMock,
            $this->checkoutAgreementsListMock,
            $this->agreementsFilterMock,
            $this->cartRepositoryMock,
            $this->storeEmulationMock
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrder()
    {
        $storeId = 1;
        $cartId = '0CQwCntNHR4yN9P5PUAzbxatvDvBXOce';
        $email = 'email@example.com';
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
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
        $this->storeEmulationMock->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with($storeId);
        $this->storeEmulationMock->expects($this->once())
            ->method('stopEnvironmentEmulation');
        $this->model->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $cartId,
            $email,
            $this->paymentMock,
            $this->addressMock
        );
    }

    public function testBeforeSavePaymentInformationAndPlaceOrderIfAgreementsNotValid()
    {
        $this->expectException('Magento\Framework\Exception\CouldNotSaveException');
        $storeId = 1;
        $cartId = '0CQwCntNHR4yN9P5PUAzbxatvDvBXOce';
        $email = 'email@example.com';
        $agreements = [1, 2, 3];
        $this->scopeConfigMock
            ->expects($this->once())
            ->method('isSetFlag')
            ->with(AgreementsProvider::PATH_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
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
        $this->storeEmulationMock->expects($this->once())
            ->method('startEnvironmentEmulation')
            ->with($storeId);
        $this->storeEmulationMock->expects($this->once())
            ->method('stopEnvironmentEmulation');
        $this->model->beforeSavePaymentInformationAndPlaceOrder(
            $this->subjectMock,
            $cartId,
            $email,
            $this->paymentMock,
            $this->addressMock
        );

        $this->expectExceptionMessage(
            "The order wasn't placed. First, agree to the terms and conditions, then try placing your order again."
        );
    }

    /**
     * Build payment extension mock.
     *
     * @return MockObject
     */
    private function getPaymentExtension(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(PaymentExtensionInterface::class)
            ->disableOriginalConstructor();
        try {
            $mockBuilder->addMethods(['getAgreementIds', 'setAgreementIds']);
        } catch (RuntimeException $e) {
            // Payment extension already generated.
        }

        return $mockBuilder->getMock();
    }
}
