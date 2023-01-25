<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Pricing\Test\Unit\Helper;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp(): void
    {
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @param string $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param string $result
     * @dataProvider currencyDataProvider
     */
    public function testCurrency($amount, $format, $includeContainer, $result)
    {
        if ($format) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convertAndFormat')
                ->with($amount, $includeContainer)
                ->willReturn($result);
        } else {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->with($amount)
                ->willReturn($result);
        }
        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->currency($amount, $format, $includeContainer));
    }

    /**
     * @return array
     */
    public function currencyDataProvider()
    {
        return [
            ['amount' => '100', 'format' => true, 'includeContainer' => true, 'result' => '100grn.'],
            ['amount' => '115', 'format' => true, 'includeContainer' => false, 'result' => '1150'],
            ['amount' => '120', 'format' => false, 'includeContainer' => null, 'result' => '1200'],
        ];
    }

    /**
     * @param string $amount
     * @param string $store
     * @param bool $format
     * @param bool $includeContainer
     * @param string $result
     * @dataProvider currencyByStoreDataProvider
     */
    public function testCurrencyByStore($amount, $store, $format, $includeContainer, $result)
    {
        if ($format) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convertAndFormat')
                ->with($amount, $includeContainer, PriceCurrencyInterface::DEFAULT_PRECISION, $store)
                ->willReturn($result);
        } else {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->with($amount, $store)
                ->willReturn($result);
        }
        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->currencyByStore($amount, $store, $format, $includeContainer));
    }

    /**
     * @return array
     */
    public function currencyByStoreDataProvider()
    {
        return [
            ['amount' => '10', 'store' => 1, 'format' => true, 'includeContainer' => true, 'result' => '10grn.'],
            ['amount' => '115', 'store' => 4,  'format' => true, 'includeContainer' => false, 'result' => '1150'],
            ['amount' => '120', 'store' => 5,  'format' => false, 'includeContainer' => null, 'result' => '1200'],
        ];
    }

    /**
     * Get helper instance
     *
     * @param array $arguments
     * @return Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject(Data::class, $arguments);
    }
}
