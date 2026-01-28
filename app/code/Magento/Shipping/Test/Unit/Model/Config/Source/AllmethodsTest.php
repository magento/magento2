<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Shipping\Test\Unit\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Config\Source\Allmethods;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Allmethods Class
 */
class AllmethodsTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject $scopeConfig
     */
    private $scopeConfig;

    /**
     * @var Config|MockObject $shippingConfig
     */
    private $shippingConfig;

    /**
     * @var Allmethods $allmethods
     */
    private $allmethods;

    /**
     * @var MockObject
     */
    private $carriersMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->shippingConfig = $this->createMock(Config::class);
        $this->carriersMock = $this->getMockBuilder(AbstractCarrierInterface::class)
            ->addMethods(['getAllowedMethods'])
            ->onlyMethods(['isActive'])
            ->getMockForAbstractClass();

        $this->allmethods = new Allmethods(
            $this->scopeConfig,
            $this->shippingConfig
        );
    }

    /**
     * Ensure that options converted correctly when isActiveOnlyFlag=false
     *
     * @dataProvider getCarriersMethodsProvider
     * @param array $expectedArray
     * @return void
     */
    public function testToOptionArrayGetAllCarriers(array $expectedArray): void
    {
        $expectedArray['getAllCarriers'] = [$this->carriersMock];

        $this->shippingConfig->expects($this->once())
            ->method('getAllCarriers')
            ->willReturn($expectedArray['getAllCarriers']);
        $this->carriersMock->expects($this->once())
            ->method('getAllowedMethods')
            ->willReturn($expectedArray['allowedMethods']);
        $this->assertEquals([$expectedArray['expected_result']], $this->allmethods->toOptionArray());
    }

    /**
     * Ensure that options converted correctly when isActiveOnlyFlag=true
     *
     * @dataProvider getCarriersMethodsProvider
     * @param array $expectedArray
     * @return void
     */
    public function testToOptionArrayGetActiveCarriers(array $expectedArray): void
    {
        $expectedArray['getActiveCarriers'] = [$this->carriersMock];

        $this->shippingConfig->expects($this->once())
            ->method('getActiveCarriers')
            ->willReturn($expectedArray['getActiveCarriers']);
        $this->carriersMock->expects($this->once())
            ->method('getAllowedMethods')
            ->willReturn($expectedArray['allowedMethods']);
        $this->assertEquals([$expectedArray['expected_result']], $this->allmethods->toOptionArray(true));
    }

    /**
     * Returns providers data for test
     *
     * @return array
     */
    public static function getCarriersMethodsProvider(): array
    {
        return [
            [
                [
                    'allowedMethods' => ['' => 'method_title'],
                    'expected_result' => [ 'value' => [], 'label' => null],
                    'getAllCarriers'  => [],
                    'getActiveCarriers'  => []
                ],
                [
                    'allowedMethods' => ['method_code' => 'method_title'],
                    'expected_result' => [ 'value' => [], 'label' => 'method_code'],
                    'getAllCarriers'  => [],
                    'getActiveCarriers'  => []
                ]

            ]
        ];
    }

    /**
     * Ensures carriers with no allowed methods are skipped entirely
     *
     * @return void
     */
    public function testSkipsCarrierWhenNoAllowedMethods(): void
    {
        $this->shippingConfig->expects($this->once())
            ->method('getAllCarriers')
            ->willReturn(['flatrate' => $this->carriersMock]);

        $this->carriersMock->expects($this->once())
            ->method('getAllowedMethods')
            ->willReturn(null);

        $result = $this->allmethods->toOptionArray(false);

        $this->assertSame([
            ['value' => '', 'label' => ''],
        ], $result);
    }

    /**
     * Ensures a proper entry is added for a valid method
     *
     * @return void
     */
    public function testAddsMethodEntryForValidMethod(): void
    {
        $this->shippingConfig->expects($this->once())
            ->method('getActiveCarriers')
            ->willReturn(['flatrate' => $this->carriersMock]);

        $this->carriersMock->expects($this->once())
            ->method('getAllowedMethods')
            ->willReturn(['fixed' => 'Fixed Rate']);

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('carriers/flatrate/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('Flat Rate');

        $result = $this->allmethods->toOptionArray(true);

        $this->assertArrayHasKey('flatrate', $result);
        $this->assertSame('Flat Rate', $result['flatrate']['label']);
        $this->assertSame([
            [
                'value' => 'flatrate_fixed',
                'label' => '[flatrate] Fixed Rate',
            ]
        ], $result['flatrate']['value']);
    }

    /**
     * Ensures null/empty method codes are not added
     *
     * @return void
     */
    public function testSkipsNullAndEmptyMethodCodes(): void
    {
        $this->shippingConfig->expects($this->once())
            ->method('getAllCarriers')
            ->willReturn(['flatrate' => $this->carriersMock]);

        $this->carriersMock->expects($this->once())
            ->method('getAllowedMethods')
            ->willReturn([0 => 'Zero Title', '' => 'Empty Title']);

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('carriers/flatrate/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn('Flat Rate');

        $result = $this->allmethods->toOptionArray(false);

        $this->assertArrayHasKey('flatrate', $result);
        $this->assertSame('Flat Rate', $result['flatrate']['label']);
        $this->assertSame([], $result['flatrate']['value']);
    }
}
