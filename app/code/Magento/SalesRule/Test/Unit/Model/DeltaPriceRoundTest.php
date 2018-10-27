<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

=======
>>>>>>> upstream/2.2-develop
namespace Magento\SalesRule\Test\Unit\Model;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\SalesRule\Model\DeltaPriceRound;

/**
 * Tests for Magento\SalesRule\Model\DeltaPriceRound.
 */
class DeltaPriceRoundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $priceCurrency;

    /**
     * @var DeltaPriceRound
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->priceCurrency = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->priceCurrency->method('round')
            ->willReturnCallback(
                function ($amount) {
                    return round($amount, 2);
                }
            );

        $this->model = new DeltaPriceRound($this->priceCurrency);
    }

    /**
     * Tests rounded price based on previous rounding operation delta.
     *
     * @param array $prices
     * @param array $roundedPrices
     * @return void
     * @dataProvider roundDataProvider
     */
<<<<<<< HEAD
    public function testRound(array $prices, array $roundedPrices): void
=======
    public function testRound(array $prices, array $roundedPrices)
>>>>>>> upstream/2.2-develop
    {
        foreach ($prices as $key => $price) {
            $roundedPrice = $this->model->round($price, 'test');
            $this->assertEquals($roundedPrices[$key], $roundedPrice);
        }

        $this->model->reset('test');
    }

    /**
     * @return array
     */
<<<<<<< HEAD
    public function roundDataProvider(): array
=======
    public function roundDataProvider()
>>>>>>> upstream/2.2-develop
    {
        return [
            [
                'prices' => [1.004, 1.004],
                'rounded prices' => [1.00, 1.01],
            ],
            [
                'prices' => [1.005, 1.005],
                'rounded prices' => [1.01, 1.0],
            ],
        ];
    }

    /**
     * @return void
     */
<<<<<<< HEAD
    public function testReset(): void
=======
    public function testReset()
>>>>>>> upstream/2.2-develop
    {
        $this->assertEquals(1.44, $this->model->round(1.444, 'test'));
        $this->model->reset('test');
        $this->assertEquals(1.44, $this->model->round(1.444, 'test'));
    }

    /**
     * @return void
     */
<<<<<<< HEAD
    public function testResetAll(): void
=======
    public function testResetAll()
>>>>>>> upstream/2.2-develop
    {
        $this->assertEquals(1.44, $this->model->round(1.444, 'test1'));
        $this->assertEquals(1.44, $this->model->round(1.444, 'test2'));

        $this->model->resetAll();

        $this->assertEquals(1.44, $this->model->round(1.444, 'test1'));
        $this->assertEquals(1.44, $this->model->round(1.444, 'test2'));
    }
}
