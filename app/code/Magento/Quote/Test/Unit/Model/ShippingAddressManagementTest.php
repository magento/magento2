<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model;

use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Framework\App\Config;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteAddressValidator;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ShippingAddressManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingAddressManagementTest extends TestCase
{
    /**
     * @var ShippingAddressManagement
     */
    private $model;

    /**
     * @var MockObject
     */
    private $addressMock;

    /**
     * @var MockObject
     */
    private $quoteRepositoryMock;

    /**
     * @var MockObject
     */
    private $addressValidator;

    /**
     * @var MockObject
     */
    private $addressRepository;

    /**
     * @var MockObject
     */
    private $quote;

    /**
     * @var MockObject
     */
    private $scopeConfig;

    /**
     * @var MockObject
     */
    private $logger;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->addressMock = $this->createMock(Address::class);
        $this->quoteRepositoryMock = $this->createMock(QuoteRepository::class);
        $this->addressValidator = $this->createMock(QuoteAddressValidator::class);
        $this->addressRepository = $this->createMock(AddressRepository::class);
        $this->quote = $this->createMock(Quote::class);
        $this->scopeConfig = $this->createMock(Config::class);
        $this->logger = $this->createMock(Monolog::class);
        $this->model = $objectManager->getObject(
            ShippingAddressManagement::class,
            [
                'quoteRepository' => $this->quoteRepositoryMock,
                'addressValidator' => $this->addressValidator,
                'addressRepository' => $this->addressRepository,
                'logger' => $this->logger,
                'scopeConfig' => $this->scopeConfig,
            ]
        );
    }

    public function testAssignNewAddressWithCompanyShowDisabled()
    {
        $cartId = 666;
        $this->addressMock->expects($this->any())->method('getSaveInAddressBook')->willReturn(1);
        $this->quote->expects($this->any())->method('getShippingAddress')->willReturn($this->addressMock);
        $this->scopeConfig->expects($this->any())->method('getValue')
            ->with(ShippingAddressManagement::XML_PATH_CUSTOMER_ADDRESS_COMPANY_SHOW)
            ->willReturn(false);
        $this->addressMock->expects($this->any())->method('getCountryId')->willReturn(null);
        $this->quoteRepositoryMock->expects($this->any())->method('getActive')->willReturn($this->quote);
        $this->addressMock->expects($this->once())->method('setCompany')->with(null);

        $this->model->assign($cartId, $this->addressMock);
    }
}
