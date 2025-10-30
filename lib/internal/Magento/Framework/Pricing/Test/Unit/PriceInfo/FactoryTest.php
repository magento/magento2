<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\PriceInfo;

use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Pricing\Price\Collection;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Pricing\PriceInfo\Factory;
use Magento\Framework\Pricing\PriceInfoInterface;
use Magento\Framework\Pricing\SaleableInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\Pricing\PriceInfo\Factory
 */
class FactoryTest extends TestCase
{
    /**
     * @var ObjectManager|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var Factory
     */
    protected $factory;

    /**
     * @var Collection|MockObject
     */
    protected $pricesMock;

    /**
     * @var SaleableInterface|MockObject
     */
    protected $saleableItemMock;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * SetUp test
     */
    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->pricesMock = $this->createMock(Collection::class);
        $this->saleableItemMock = $this->getMockForAbstractClass(
            SaleableInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getQty']
        );
        $this->priceInfoMock = $this->getMockForAbstractClass(
            PriceInfoInterface::class,
            [],
            '',
            false,
            true,
            true,
            []
        );
        $this->types = [
            'default' => [
                'infoClass' => 'Price\PriceInfo\Default',
                'prices' => 'Price\Collection\Default',
            ],
            'configurable' => [
                'infoClass' => 'Price\PriceInfo\Configurable',
                'prices' => 'Price\Collection\Configurable',
            ],
        ];
        $this->factory = new Factory($this->types, $this->objectManagerMock);
    }

    /**
     * @return array
     */
    public static function createPriceInfoDataProvider()
    {
        return [
            [
                'simple',
                1,
                'Price\PriceInfo\Default',
                'Price\Collection\Default',
            ],
            [
                'configurable',
                2,
                'Price\PriceInfo\Configurable',
                'Price\Collection\Configurable'
            ]
        ];
    }

    /**
     * @param $typeId
     * @param $quantity
     * @param $infoClass
     * @param $prices
     * @dataProvider createPriceInfoDataProvider
     */
    public function testCreate($typeId, $quantity, $infoClass, $prices)
    {
        $this->saleableItemMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn($typeId);
        $this->saleableItemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($quantity);

        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap([
                [
                    $prices,
                    [
                        'saleableItem' => $this->saleableItemMock,
                        'quantity' => $quantity
                    ],
                    $this->pricesMock,
                ],
                [
                    $infoClass,
                    [
                        'saleableItem' => $this->saleableItemMock,
                        'quantity' => $quantity,
                        'prices' => $this->pricesMock
                    ],
                    $this->priceInfoMock
                ],
            ]);
        $this->assertEquals($this->priceInfoMock, $this->factory->create($this->saleableItemMock, []));
    }
}
