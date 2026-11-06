<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Customer\Model\Address\AbstractAddress\RegionModelsCache;
use Magento\Directory\Model\Region;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AddressTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var Order|MockObject
     */
    protected $orderMock;

    /**
     * @var RegionFactory|MockObject
     */
    protected $regionFactoryMock;

    /**
     * @var Region|MockObject
     */
    protected $regionMock;

    protected function setUp(): void
    {
        $this->orderMock = $this->createMock(Order::class);
        $this->orderMock = $this->createMock(Order::class);
        $this->regionFactoryMock = $this->createMock(RegionFactory::class);
        $this->regionMock = $this->createPartialMockWithReflection(
            Region::class,
            ['getCountryId', 'getCode', 'load']
        );
        $objectManager = new ObjectManager($this);
        $this->address = $objectManager->getObject(
            Address::class,
            [
                'regionFactory' => $this->regionFactoryMock
            ]
        );
    }

    public function testSetOrder()
    {
        $this->assertEquals($this->address, $this->address->setOrder($this->orderMock));
    }

    public function testGetRegionCodeRegionIsSet()
    {
        $regionId = 1;
        $this->address->setData('region', 'region');
        $this->address->setData('region_id', $regionId);
        $this->address->setData('country_id', 2);

        $this->regionMock->expects(static::once())
            ->method('load')
            ->with($regionId)
            ->willReturnSelf();

        $this->regionMock->expects(static::once())
            ->method('getCountryId')
            ->willReturn(1);

        $this->regionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->assertEquals('region', $this->address->getRegionCode());
    }

    /**
     * @return array
     */
    public static function regionProvider()
    {
        return [ [1, null], [null, 1]];
    }

    #[DataProvider('regionProvider')]
    public function testGetRegionCodeRegion($region, $regionId)
    {
        $this->address->setData('region', $region);
        $this->address->setData('region_id', $regionId);
        $this->address->setData('country_id', 1);
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(1);
        $this->regionMock->expects($this->once())
            ->method('getCode')
            ->willReturn('region');
        $this->assertEquals('region', $this->address->getRegionCode());
    }

    public function testGetRegionCodeRegionFailure()
    {
        $this->address->setData('region', 1);
        $this->address->setData('region_id', 1);
        $this->address->setData('country_id', 1);
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('load')
            ->with(1)
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('getCountryId')
            ->willReturn(2);
        $this->regionMock->expects($this->never())
            ->method('getCode');
        $this->assertNull($this->address->getRegionCode());
    }

    public function testGetRegionCodeLoadsEachRegionOncePerRequest(): void
    {
        $objectManager = new ObjectManager($this);
        $regionModelsCache = new RegionModelsCache();
        $this->regionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->regionMock);
        $this->regionMock->expects($this->once())
            ->method('load')
            ->with(12)
            ->willReturnSelf();
        $this->regionMock->method('getCountryId')->willReturn('US');
        $this->regionMock->method('getCode')->willReturn('CA');

        foreach (['billing', 'shipping'] as $type) {
            $address = $objectManager->getObject(
                Address::class,
                [
                    'regionFactory' => $this->regionFactoryMock,
                    'regionModelsCache' => $regionModelsCache,
                ]
            );
            $address->setData(['address_type' => $type, 'region_id' => 12, 'country_id' => 'US']);
            $this->assertSame('CA', $address->getRegionCode());
            $this->assertSame('CA', $address->getRegionCode());
        }
    }

    public function testGetRegionCodeDoesNotReuseRegionOfAnotherId(): void
    {
        $objectManager = new ObjectManager($this);
        $regionModelsCache = new RegionModelsCache();
        $california = $this->createPartialMockWithReflection(Region::class, ['getCountryId', 'getCode', 'load']);
        $california->method('load')->willReturnSelf();
        $california->method('getCountryId')->willReturn('US');
        $california->method('getCode')->willReturn('CA');
        $texas = $this->createPartialMockWithReflection(Region::class, ['getCountryId', 'getCode', 'load']);
        $texas->method('load')->willReturnSelf();
        $texas->method('getCountryId')->willReturn('US');
        $texas->method('getCode')->willReturn('TX');
        $this->regionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls($california, $texas);

        $codes = [];
        foreach ([12, 57, 12] as $regionId) {
            $address = $objectManager->getObject(
                Address::class,
                [
                    'regionFactory' => $this->regionFactoryMock,
                    'regionModelsCache' => $regionModelsCache,
                ]
            );
            $address->setData(['region_id' => $regionId, 'country_id' => 'US']);
            $codes[] = $address->getRegionCode();
        }
        $this->assertSame(['CA', 'TX', 'CA'], $codes);
    }

    /**
     * @return array
     */
    public static function regionCacheProvider(): array
    {
        return [
            'numeric region without region id' => [['region' => '12', 'country_id' => 'US'], 'CA', 1, false],
            'region of another country' => [
                ['region_id' => 12, 'region' => 'Bayern', 'country_id' => 'DE'],
                'Bayern',
                1,
                false,
            ],
            'another country without region string' => [['region_id' => 12, 'country_id' => 'DE'], null, 1, false],
            'empty region id bypasses the cache' => [
                ['region_id' => null, 'region' => 'London', 'country_id' => 'GB'],
                'London',
                2,
                false,
            ],
            'customer cache entry is not reused' => [['region_id' => 12, 'country_id' => 'US'], 'CA', 1, true],
        ];
    }

    #[DataProvider('regionCacheProvider')]
    public function testGetRegionCodeWithSharedCache(
        array $addressData,
        ?string $expectedCode,
        int $expectedLoads,
        bool $hasCustomerEntry
    ): void {
        $objectManager = new ObjectManager($this);
        $regionModelsCache = new RegionModelsCache();
        if ($hasCustomerEntry) {
            $regionModelsCache->add('12', $this->createRegion('US', 'XX'));
        }
        $this->regionFactoryMock->expects($this->exactly($expectedLoads))
            ->method('create')
            ->willReturnCallback(fn () => $this->createRegion('US', 'CA', 12));

        foreach (['billing', 'shipping'] as $type) {
            $address = $objectManager->getObject(
                Address::class,
                [
                    'regionFactory' => $this->regionFactoryMock,
                    'regionModelsCache' => $regionModelsCache,
                ]
            );
            $address->setData($addressData + ['address_type' => $type]);
            $this->assertSame($expectedCode, $address->getRegionCode());
        }
        if ($hasCustomerEntry) {
            $this->assertSame('XX', $regionModelsCache->get('12')->getCode());
        }
    }

    /**
     * Region mock that only carries data once loaded with its own id, when one is given
     */
    private function createRegion(string $countryId, string $code, ?int $regionId = null): Region
    {
        $loadedId = $regionId === null ? null : false;
        $region = $this->createPartialMockWithReflection(Region::class, ['getCountryId', 'getCode', 'load']);
        $region->method('load')->willReturnCallback(
            function ($id) use (&$loadedId, $region) {
                $loadedId = $id;
                return $region;
            }
        );
        $isLoaded = function () use (&$loadedId, $regionId): bool {
            return $regionId === null || (int)$loadedId === $regionId;
        };
        $region->method('getCountryId')->willReturnCallback(fn () => $isLoaded() ? $countryId : null);
        $region->method('getCode')->willReturnCallback(fn () => $isLoaded() ? $code : null);
        return $region;
    }

    public function testGetName()
    {
        $this->address->setData('suffix', 'suffix');
        $this->address->setData('prefix', 'prefix');
        $this->address->setData('firstname', 'firstname');
        $this->address->setData('middlename', 'middlename');
        $this->address->setData('lastname', 'lastname');
        $this->assertEquals('prefix firstname middlename lastname suffix', $this->address->getName());
    }
}
