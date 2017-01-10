<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Pricing\Test\Unit\Helper;

use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    protected function setUp()
    {
        $this->priceCurrencyMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
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
                ->will($this->returnValue($result));
        } else {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->with($amount)
                ->will($this->returnValue($result));
        }
        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->currency($amount, $format, $includeContainer));
    }

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
                ->will($this->returnValue($result));
        } else {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->with($amount, $store)
                ->will($this->returnValue($result));
        }
        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->currencyByStore($amount, $store, $format, $includeContainer));
    }

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
        return $this->objectManager->getObject(\Magento\Framework\Pricing\Helper\Data::class, $arguments);
    }
}
