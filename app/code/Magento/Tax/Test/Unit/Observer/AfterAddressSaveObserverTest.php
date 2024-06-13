<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Observer\AfterAddressSaveObserver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Observer|MockObject
     */
    protected $observerMock;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Module manager
     *
     * @var Manager|MockObject
     */
    private $moduleManagerMock;

    /**
     * Cache config
     *
     * @var Config|MockObject
     */
    private $cacheConfigMock;

    /**
     * @var Data|MockObject
     */
    private $taxHelperMock;

    /**
     * @var TaxAddressManagerInterface|MockObject
     */
    private $addressManagerMock;

    /**
     * @var AfterAddressSaveObserver
     */
    private $session;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createPartialMockWithReflection(
            Observer::class,
            ['getCustomerAddress']
        );

        $this->moduleManagerMock = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxHelperMock = $this->getMockBuilder(Data::class)
            ->onlyMethods(['isCatalogPriceDisplayAffectedByTax'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->addressManagerMock = $this->createMock(TaxAddressManagerInterface::class);

        $this->session = $this->objectManager->getObject(
            AfterAddressSaveObserver::class,
            [
                'taxHelper' => $this->taxHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
                'addressManager' => $this->addressManagerMock,
            ]
        );
    }

    /**
     * @test
     */
    #[DataProvider('getExecuteDataProvider')]
    public function testExecute(
        bool $isEnabledPageCache,
        bool $isEnabledConfigCache,
        bool $isCatalogPriceDisplayAffectedByTax,
        bool $isNeedSetAddress
    ): void {
        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($isEnabledPageCache);

        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isEnabledConfigCache);

        $this->taxHelperMock->expects($this->any())
            ->method('isCatalogPriceDisplayAffectedByTax')
            ->willReturn($isCatalogPriceDisplayAffectedByTax);

        /* @var \Magento\Customer\Model\Address|MockObject $address */
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock->expects($this->any())
            ->method('getCustomerAddress')
            ->willReturn($address);

        $this->addressManagerMock->expects($isNeedSetAddress ? $this->once() : $this->never())
            ->method('setDefaultAddressAfterSave')
            ->with($address);

        $this->session->execute($this->observerMock);
    }

    /**
     * @return array
     */
    public static function getExecuteDataProvider(): array
    {
        return [
            [false, false, false, false],
            [false, false, true, false],
            [false, true, false, false],
            [false, true, true, false],
            [true, false, false, false],
            [true, false, true, false],
            [true, true, false, false],
            [true, true, true, true],
        ];
    }
}
