<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\CarrierFactory;
use Magento\Shipping\Model\Shipping;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ShippingRequestIsolationTest extends TestCase
{
    /**
     * @param array|null $limitCarrier
     * @return void
     */
    #[DataProvider('limitCarrierProvider')]
    public function testCarriersReceiveIsolatedRequests(?array $limitCarrier): void
    {
        $firstCarrier = $this->createMock(AbstractCarrierOnline::class);
        $secondCarrier = $this->createMock(AbstractCarrierOnline::class);
        foreach ([$firstCarrier, $secondCarrier] as $carrier) {
            $carrier->method('checkAvailableShipCountries')->willReturnSelf();
            $carrier->method('processAdditionalValidation')->willReturnSelf();
            $carrier->method('getConfigData')->willReturn(false);
        }

        $carrierFactory = $this->createMock(CarrierFactory::class);
        $carrierFactory->expects($this->exactly(2))->method('create')->willReturnMap([
            ['first', 1, $firstCarrier],
            ['second', 1, $secondCarrier],
        ]);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $scopeConfig->method('isSetFlag')->willReturn(true);
        $scopeConfig->method('getValue')->willReturnMap([
            ['carriers', ScopeInterface::SCOPE_STORE, 1, ['first' => ['active' => 1], 'second' => ['active' => 1]]],
            ['carriers/first', ScopeInterface::SCOPE_STORE, 1, ['active' => 1]],
            ['carriers/second', ScopeInterface::SCOPE_STORE, 1, ['active' => 1]],
        ]);
        $shipping = (new ObjectManagerHelper($this))->getObject(Shipping::class, [
            'carrierFactory' => $carrierFactory,
            'scopeConfig' => $scopeConfig,
        ]);
        $request = new RateRequest([
            'orig' => true,
            'store_id' => 1,
            'limit_carrier' => $limitCarrier,
            'free_shipping' => false,
            'package_value' => 100,
            'packages' => [10 => 2],
        ]);
        $firstRequest = null;
        $secondRequest = null;
        $firstCarrier->expects($this->once())->method('collectRates')->willReturnCallback(
            static function (RateRequest $carrierRequest) use (&$firstRequest): bool {
                $firstRequest = $carrierRequest;
                $carrierRequest->setFreeShipping(true);
                $carrierRequest->setPackageValue(0);
                $carrierRequest->setPackages([5 => 4]);
                return false;
            }
        );
        $secondCarrier->expects($this->once())->method('collectRates')->willReturnCallback(
            static function (RateRequest $carrierRequest) use (&$secondRequest): bool {
                $secondRequest = $carrierRequest;
                return false;
            }
        );

        $shipping->collectRates($request);

        $this->assertInstanceOf(RateRequest::class, $firstRequest);
        $this->assertInstanceOf(RateRequest::class, $secondRequest);
        $this->assertNotSame($firstRequest, $secondRequest);
        $this->assertNotSame($request, $firstRequest);
        $this->assertNotSame($request, $secondRequest);
        $this->assertFalse($secondRequest->getFreeShipping());
        $this->assertSame(100, $secondRequest->getPackageValue());
        $this->assertSame([10 => 2], $secondRequest->getPackages());
        $this->assertFalse($request->getFreeShipping());
        $this->assertSame(100, $request->getPackageValue());
        $this->assertSame([10 => 2], $request->getPackages());
    }

    /**
     * @return array
     */
    public static function limitCarrierProvider(): array
    {
        return [
            'configured carriers' => [null],
            'limited carriers' => [['first', 'second']],
        ];
    }
}
