<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Observer\AfterAddressSaveObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveObserverTest extends TestCase
{
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
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerAddress'])
            ->getMock();

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

        $this->addressManagerMock = $this->getMockBuilder(TaxAddressManagerInterface::class)
            ->onlyMethods(['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
     * @dataProvider getExecuteDataProvider
     *
     * @param bool $isEnabledPageCache
     * @param bool $isEnabledConfigCache
     * @param bool $isCatalogPriceDisplayAffectedByTax
     * @param bool $isNeedSetAddress
     */
    public function testExecute(
        $isEnabledPageCache,
        $isEnabledConfigCache,
        $isCatalogPriceDisplayAffectedByTax,
        $isNeedSetAddress
    ) {
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
    public static function getExecuteDataProvider()
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
