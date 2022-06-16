<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Shipping\Model\Config;

/**
 * Test for \Magento\Shipping\Model\Config.
 */
class ConfigTest extends TestCase
{
    private const STUB_STORE_CODE = 'default';

    /**
     * @var array
     */
    private $shippingCarriersData = [
        'flatrate' => [
            'active' => '1',
            'name' => 'Fixed',
            'title' => 'Flat Rate',
        ],
        'tablerate' => [
            'active' => '0',
            'name' => 'Table Rate',
            'title' => 'Best Way',
        ]
    ];

    /**
     * @var Config
     */
    private $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var CarrierFactory|MockObject
     */
    private $carrierFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->carrierFactoryMock = $this->createMock(CarrierFactory::class);

        $this->model = new Config($this->scopeConfigMock, $this->carrierFactoryMock, []);
    }

    /**
     * Get active carriers when there is no active on the store
     *
     * @return void
     */
    public function testGetActiveCarriersWhenThereIsNoAvailable(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('carriers', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(null);

        $this->assertEquals([], $this->model->getActiveCarriers());
    }

    /**
     * Test for getActiveCarriers
     *
     * @return void
     */
    public function testGetActiveCarriers(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('carriers', ScopeInterface::SCOPE_STORE, self::STUB_STORE_CODE)
            ->willReturn($this->shippingCarriersData);

        $this->scopeConfigMock->expects($this->exactly(2))
            ->method('isSetFlag')
            ->withConsecutive(
                ['carriers/flatrate/active', ScopeInterface::SCOPE_STORE, self::STUB_STORE_CODE],
                ['carriers/tablerate/active', ScopeInterface::SCOPE_STORE, self::STUB_STORE_CODE],
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false,
            );

        $this->carrierFactoryMock->expects($this->once())
            ->method('create')
            ->with('flatrate', self::STUB_STORE_CODE)
            ->willReturn(true);

        $this->assertEquals(['flatrate' => true], $this->model->getActiveCarriers(self::STUB_STORE_CODE));
    }

    /**
     * Get all carriers when there is no carriers available on the store
     *
     * @return void
     */
    public function testGetAllCarriersWhenThereIsNoAvailable(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('carriers', ScopeInterface::SCOPE_STORE, null)
            ->willReturn(null);

        $this->assertEquals([], $this->model->getAllCarriers());
    }

    /**
     * Test for getAllCarriers
     *
     * @return void
     */
    public function testGetAllCarriers(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with('carriers', ScopeInterface::SCOPE_STORE, self::STUB_STORE_CODE)
            ->willReturn($this->shippingCarriersData);

        $this->carrierFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->withConsecutive(
                ['flatrate', self::STUB_STORE_CODE],
                ['tablerate', self::STUB_STORE_CODE],
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false,
            );

        $this->assertEquals(['flatrate' => true], $this->model->getAllCarriers(self::STUB_STORE_CODE));
    }
}
