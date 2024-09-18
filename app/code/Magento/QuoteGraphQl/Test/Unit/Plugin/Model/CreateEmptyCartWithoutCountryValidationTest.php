<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Test\Unit\Plugin\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\QuoteGraphQl\Plugin\Model\CreateEmptyCartWithoutCountryValidation;
use Magento\Store\Model\Store;
use Magento\Customer\Api\Data\CustomerInterface as Customer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateEmptyCartWithoutCountryValidationTest extends TestCase
{
    /**
     * @var MockObject|StoreManagerInterface
     */
    private MockObject|StoreManagerInterface $storeManager;

    /**
     * @var MockObject|CartRepositoryInterface
     */
    private MockObject|CartRepositoryInterface $quoteRepository;

    /**
     * @var MockObject|CustomerRepositoryInterface
     */
    private MockObject|CustomerRepositoryInterface $customerRepository;

    /**
     * @var MockObject|QuoteFactory
     */
    private MockObject|QuoteFactory $quoteFactory;

    /**
     * @var CreateEmptyCartWithoutCountryValidation
     */
    private CreateEmptyCartWithoutCountryValidation $model;

    /**
     * @var MockObject|QuoteManagement
     */
    private MockObject|QuoteManagement $quoteManagement;

    /**
     * @var MockObject|Store
     */
    private MockObject|Store $store;

    /**
     * @var MockObject|Quote
     */
    private MockObject|Quote $quote;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private MockObject|ScopeConfigInterface $scopeConfig;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->quoteFactory = $this->createMock(QuoteFactory::class);
        $this->quoteManagement = $this->createMock(QuoteManagement::class);
        $this->store = $this->getMockBuilder(Store::class)
            ->addMethods(['getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quote = $this->createMock(Quote::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->model = new CreateEmptyCartWithoutCountryValidation(
            $this->storeManager,
            $this->quoteRepository,
            $this->customerRepository,
            $this->quoteFactory,
            $this->scopeConfig
        );
    }

    /**
     * @dataProvider aroundCreateEmptyCartForCustomerDataProvider
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testAroundCreateEmptyCartForCustomerCreatesNewCart(
        int $storeId,
        int $customerId,
        object $callBack
    ) {
        $expectedResult = 123;
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);

        $this->store->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->quoteRepository->expects($this->once())
            ->method('getActiveForCustomer')
            ->willThrowException(new NoSuchEntityException(__('No such entity')));

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->createMock(Customer::class));

        $this->quoteFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->quote);

        $this->quote->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->quote->expects($this->once())
            ->method('setCustomer')
            ->with($this->isInstanceOf(Customer::class));
        $this->quote->expects($this->once())
            ->method('setCustomerIsGuest')
            ->with(0);

        $this->quoteRepository->expects($this->once())
            ->method('save')
            ->with($this->quote);

        $this->quote->expects($this->once())
            ->method('getId')
            ->willReturn(123);

        $result = $this->model->aroundCreateEmptyCartForCustomer(
            $this->quoteManagement,
            $callBack,
            $customerId
        );

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider aroundCreateEmptyCartForCustomerDataProvider
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws LocalizedException
     */
    public function testAroundCreateEmptyCartForCustomerHandlesSaveException(
        int $storeId,
        int $customerId,
        object $callBack
    ) {
        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->quoteRepository->expects($this->once())
            ->method('getActiveForCustomer')
            ->willThrowException(new NoSuchEntityException(__('No such entity')));

        $this->customerRepository->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($this->createMock(Customer::class));

        $this->quoteFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->quote);

        $this->quote->expects($this->once())
            ->method('setStoreId')
            ->with($storeId);
        $this->quote->expects($this->once())
            ->method('setCustomer')
            ->with($this->isInstanceOf(Customer::class));
        $this->quote->expects($this->once())
            ->method('setCustomerIsGuest')
            ->with(0);

        $this->quoteRepository->expects($this->once())
            ->method('save')
            ->willThrowException(new CouldNotSaveException(__('The quote can\'t be created.')));

        $this->expectException(CouldNotSaveException::class);

        $this->model->aroundCreateEmptyCartForCustomer(
            $this->quoteManagement,
            $callBack,
            $customerId
        );
    }

    /**
     * @return array
     */
    public static function aroundCreateEmptyCartForCustomerDataProvider(): array
    {
        return [
            [1, 1, function () {
            }]
        ];
    }
}
