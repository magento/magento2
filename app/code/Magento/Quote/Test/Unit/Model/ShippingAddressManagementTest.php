<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\ShippingAddressManagement;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShippingAddressManagementTest extends TestCase
{
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
        $this->model = new ShippingAddressManagement(
            $this->quoteRepositoryMock,
            $this->addressValidatorMock,
            $this->loggerMock,
            $this->addressRepositoryMock,
            $this->scopeConfigMock,
            $this->totalsCollectorMock
        );
    }

    /**
     * @throws InputException
     * @throws NoSuchEntityException
     * @dataProvider assignDataProvider
     */
    public function testAssign(bool $saveInAddressBook, bool $showCompany): void
    {
        $cartId = $customerId = 123;
        $addressMock = $this->getMockBuilder(AddressInterface::class)
            ->addMethods(['setCollectShippingRates', 'save', 'importCustomerAddressData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
        $addressMock
            ->expects($saveInAddressBook && !$showCompany ? $this->once() : $this->never())
            ->method('setCompany')
            ->with(null);
        $addressMock
            ->expects($this->once())
            ->method('importCustomerAddressData')
            ->willReturn($addressMock);
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
