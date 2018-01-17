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
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $checkoutAgreementsListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

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
        $repositoryMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface::class
        );

        $this->checkoutAgreementsListMock = $this->createMock(
            \Magento\CheckoutAgreements\Api\CheckoutAgreementsListInterface::class
        );
        $this->filterBuilderMock = $this->createMock(\Magento\Framework\Api\FilterBuilder::class);
        $this->searchCriteriaBuilderMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->model = new \Magento\CheckoutAgreements\Model\Checkout\Plugin\Validation(
            $this->agreementsValidatorMock,
            $this->scopeConfigMock,
            $repositoryMock,
            $this->checkoutAgreementsListMock,
            $this->filterBuilderMock,
            $this->searchCriteriaBuilderMock,
            $this->storeManagerMock
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
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($this->buildSearchCriteria())
            ->willReturn([1]);
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
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($this->buildSearchCriteria())
            ->willReturn([1]);
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
        $this->checkoutAgreementsListMock->expects($this->once())
            ->method('getList')
            ->with($this->buildSearchCriteria())
            ->willReturn([1]);
        $this->extensionAttributesMock->expects($this->once())->method('getAgreementIds')->willReturn($agreements);
        $this->agreementsValidatorMock->expects($this->once())->method('isValid')->with($agreements)->willReturn(true);
        $this->paymentMock->expects(static::atLeastOnce())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensionAttributesMock);
        $this->model->beforeSavePaymentInformation($this->subjectMock, $cartId, $this->paymentMock, $this->addressMock);
    }

    /**
     * Build mock object for search criteria
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function buildSearchCriteria() : \PHPUnit_Framework_MockObject_MockObject
    {
        $storeId = 1;
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue($storeId));
        $this->storeManagerMock->expects($this->any())->method('getStore')->will($this->returnValue($storeMock));

        $storeFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $activeFilterMock = $this->createMock(\Magento\Framework\Api\Filter::class);

        $this->filterBuilderMock->expects($this->at(0))->method('setField')->with('store_id')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(1))->method('setConditionType')->with('eq')->willReturnSelf();

        $this->filterBuilderMock->expects($this->at(2))->method('setValue')->with($storeId)->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(3))->method('create')->willReturn($storeFilterMock);

        $this->filterBuilderMock->expects($this->at(4))->method('setField')->with('is_active')->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(5))->method('setConditionType')->with('eq')->willReturnSelf();

        $this->filterBuilderMock->expects($this->at(6))->method('setValue')->with(1)->willReturnSelf();
        $this->filterBuilderMock->expects($this->at(7))->method('create')->willReturn($activeFilterMock);

        $this->searchCriteriaBuilderMock->expects($this->at(0))
            ->method('addFilters')
            ->with([$storeFilterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->at(1))
            ->method('addFilters')
            ->with([$activeFilterMock])
            ->willReturnSelf();
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilderMock->expects($this->at(2))->method('create')->willReturn($searchCriteriaMock);
        return $searchCriteriaMock;
    }
}
