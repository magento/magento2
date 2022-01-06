<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model\Checkout\Plugin;

use Magento\Checkout\Api\AgreementsValidatorInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface;
use Magento\CheckoutAgreements\Model\AgreementsProvider;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\ActiveStoreAgreementsFilter;
use Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentExtension;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

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
        $this->agreementsValidatorMock = $this->getMockForAbstractClass(AgreementsValidatorInterface::class);
        $this->subjectMock = $this->getMockForAbstractClass(PaymentInformationManagementInterface::class);
        $this->paymentMock = $this->getMockForAbstractClass(PaymentInterface::class);
        $this->addressMock = $this->getMockForAbstractClass(AddressInterface::class);
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['getIsMultiShipping'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteRepositoryMock = $this->getMockForAbstractClass(CartRepositoryInterface::class);
        $this->extensionAttributesMock = $this->getPaymentExtension();
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
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
            ->method('getIsMultiShipping')
            ->willReturn(false);
        $this->quoteRepositoryMock
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
        $this->model->beforeSavePaymentInformationAndPlaceOrder(
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
            ->method('getIsMultiShipping')
            ->willReturn(false);
        $this->quoteRepositoryMock
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

    /**
     * Build payment extension mock.
     *
     * @return MockObject
     */
    private function getPaymentExtension(): MockObject
    {
        $mockBuilder = $this->getMockBuilder(PaymentExtension::class);
        try {
            $mockBuilder->addMethods(['getAgreementIds']);
        } catch (RuntimeException $e) {
            // Payment extension already generated.
        }

        return $mockBuilder->getMock();
    }
}
