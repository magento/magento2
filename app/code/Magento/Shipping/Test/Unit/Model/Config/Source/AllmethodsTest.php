<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Carrier\AbstractCarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Config\Source\Allmethods;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Tests for Allmethods Class
 */
class InfoTest extends \PHPUnit\Framework\TestCase
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
    protected function setUp()
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->shippingConfig = $this->createMock(Config::class);
        $this->carriersMock = $this->getMockBuilder(AbstractCarrierInterface::class)
            ->setMethods(
                [
                    'isActive',
                    'getAllowedMethods'
                ]
            )->getMockForAbstractClass();

        $this->allmethods = new Allmethods(
            $this->scopeConfig,
            $this->shippingConfig
        );
    }

    /**
     * Ensure that options converted correctly
     *
     * @dataProvider getCarriersMethodsProvider
     * @param array $expectedArray
     * @return void
     */
    public function testToOptionArray(array $expectedArray): void
    {
        $expectedArray['getAllCarriers'] = [$this->carriersMock];

        $this->shippingConfig->expects($this->once())
                             ->method('getAllCarriers')
                             ->willReturn($expectedArray['getAllCarriers']);
        $this->carriersMock->expects($this->once())
                             ->method('isActive')
                             ->willReturn(true);
        $this->carriersMock->expects($this->once())
                             ->method('getAllowedMethods')
                             ->willReturn($expectedArray['allowedMethods']);
        $this->assertEquals([$expectedArray['expected_result']], $this->allmethods->toOptionArray());
    }

    /**
     * Returns providers data for test
     *
     * @return array
     */
    public function getCarriersMethodsProvider(): array
    {
        return [
            [
                [
                    'allowedMethods' => [null => 'method_title'],
                    'expected_result' => [ 'value' => [], 'label' => null],
                    'getAllCarriers'  => []
                ],
                [
                    'allowedMethods' => ['method_code' => 'method_title'],
                    'expected_result' => [ 'value' => [], 'label' => 'method_code'],
                    'getAllCarriers'  => []
                ]

            ]
        ];
    }
}
