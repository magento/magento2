<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Observer;

use Magento\Customer\Model\Address;
use Magento\Framework\Event\Observer;
use Magento\Framework\Module\Manager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\PageCache\Model\Config;
use Magento\Tax\Api\TaxAddressManagerInterface;
use Magento\Weee\Helper\Data;
use Magento\Weee\Observer\AfterAddressSave;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests to cover AfterAddressSave
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AfterAddressSaveTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

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
    private $weeeHelperMock;

    /**
     * @var TaxAddressManagerInterface|MockObject
     */
    private $addressManagerMock;

    /**
     * @var AfterAddressSave
     */
    protected $session;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->observerMock = $this->createPartialMockWithReflection(Observer::class, ['getCustomerAddress']);

        $this->moduleManagerMock = $this->createMock(Manager::class);

        $this->cacheConfigMock = $this->createMock(Config::class);

        $this->weeeHelperMock = $this->createMock(Data::class);

        $this->addressManagerMock = $this->createPartialMock(
            TaxAddressManagerInterface::class,
            ['setDefaultAddressAfterSave', 'setDefaultAddressAfterLogIn']
        );

        $this->session = $this->objectManager->getObject(
            AfterAddressSave::class,
            [
                'weeeHelper' => $this->weeeHelperMock,
                'moduleManager' => $this->moduleManagerMock,
                'cacheConfig' => $this->cacheConfigMock,
                'addressManager' => $this->addressManagerMock,
            ]
        );
    }

    /**
     * @test
     *
     * @param $isEnabledPageCache
     * @param $isEnabledConfigCache
     * @param $isEnabledWeee
     * @param $isNeedSetAddress
     */
    #[DataProvider('getExecuteDataProvider')]
    public function testExecute(
        $isEnabledPageCache,
        $isEnabledConfigCache,
        $isEnabledWeee,
        $isNeedSetAddress
    ) {
        $this->moduleManagerMock->expects($this->any())
            ->method('isEnabled')
            ->with('Magento_PageCache')
            ->willReturn($isEnabledPageCache);

        $this->cacheConfigMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isEnabledConfigCache);

        $this->weeeHelperMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($isEnabledWeee);

        /** @var \Magento\Customer\Model\Address|MockObject $address */
        $address = $this->createMock(Address::class);

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
