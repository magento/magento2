<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface as CustomerAddressInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\QuoteAddressValidationService;
use Magento\Quote\Model\ShippingAddressManagement;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Summary of ShippingAddressManagementTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingAddressManagementTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ShippingAddressManagement
     */
    private ShippingAddressManagement $model;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepositoryMock;

    /**
     * @var QuoteAddressValidator
     */
    private $addressValidatorMock;

    /**
     * @var LoggerInterface
     */
    private $loggerMock;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepositoryMock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfigMock;

    /**
     * @var TotalsCollector
     */
    private $totalsCollectorMock;

    /**
     * @var Quote
     */
    private $quoteMock;

    /**
     * @var QuoteAddressValidationService
     */
    private $quoteAddressValidationServiceMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->addressValidatorMock = $this->createMock(QuoteAddressValidator::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->addressRepositoryMock = $this->createMock(AddressRepositoryInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->totalsCollectorMock = $this->createMock(TotalsCollector::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->quoteAddressValidationServiceMock = $this->createMock(QuoteAddressValidationService::class);
        $this->model = new ShippingAddressManagement(
            $this->quoteRepositoryMock,
            $this->addressValidatorMock,
            $this->loggerMock,
            $this->addressRepositoryMock,
            $this->scopeConfigMock,
            $this->totalsCollectorMock,
            $this->quoteAddressValidationServiceMock
        );
    }

    /**
     * @throws InputException
     * @throws NoSuchEntityException
     */
    #[DataProvider('assignDataProvider')]
    public function testAssign(bool $saveInAddressBook, bool $showCompany): void
    {
        $cartId = $customerId = 123;
        $addressMock = $this->createPartialMockWithReflection(
            Address::class,
            [
                'importCustomerAddressData',
                'getSaveInAddressBook',
                'getSameAsBilling',
                'getCustomerAddressId',
                'setCompany',
                'setSameAsBilling',
                'setSaveInAddressBook',
                'setCollectShippingRates',
                'save'
            ]
        );
        $this->quoteMock
            ->expects($this->once())
            ->method('isVirtual')
            ->willReturn(false);
        $this->quoteRepositoryMock
            ->expects($this->once())
            ->method('getActive')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        $addressMock
            ->expects($this->once())
            ->method('getSaveInAddressBook')
            ->willReturn($saveInAddressBook);
        $addressMock
            ->expects($this->once())
            ->method('getSameAsBilling')
            ->willReturn(true);
        $addressMock
            ->expects($this->once())
            ->method('getCustomerAddressId')
            ->willReturn($customerId);
        $customerAddressMock = $this->createMock(CustomerAddressInterface::class);
        $this->addressRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with($customerId)
            ->willReturn($customerAddressMock);
        $addressMock
            ->expects($saveInAddressBook && !$showCompany ? $this->once() : $this->never())
            ->method('setCompany')
            ->with(null);
        $addressMock
            ->expects($this->once())
            ->method('importCustomerAddressData')
            ->with($customerAddressMock)
            ->willReturnSelf();
        $addressMock
            ->expects($this->once())
            ->method('setSameAsBilling')
            ->with(true);
        $addressMock
            ->expects($this->once())
            ->method('setSaveInAddressBook')
            ->with($saveInAddressBook);
        $addressMock->method('setCollectShippingRates');
        $addressMock->method('save');
        $this->scopeConfigMock
            ->expects($saveInAddressBook ? $this->once() : $this->never())
            ->method('getValue')
            ->willReturn($showCompany);
        $this->addressValidatorMock
            ->expects($this->once())
            ->method('validateForCart');
        $this->quoteMock
            ->expects($this->once())
            ->method('setShippingAddress')
            ->with($addressMock);
        $this->quoteMock
            ->method('getShippingAddress')
            ->willReturn($addressMock);
        $this->quoteAddressValidationServiceMock
            ->expects($this->once())
            ->method('validateAddressesWithRules')
            ->with(
                $this->isInstanceOf(Quote::class),
                $addressMock,
                null
            );
        $this->model->assign($cartId, $addressMock);
    }

    /**
     * @return array
     */
    public static function assignDataProvider(): array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];
    }
}
