<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Data
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrency;

    public function setUp()
    {
        $this->priceCurrency = $this->getMock('Magento\Framework\Pricing\PriceCurrencyInterface');

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject('Magento\Core\Helper\Data', array(
            'priceCurrency' => $this->priceCurrency
        ));
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
            $this->priceCurrency->expects($this->once())
                ->method('convertAndFormat')
                ->with($amount, $includeContainer)
                ->will($this->returnValue($result));
        } else {
            $this->priceCurrency->expects($this->once())
                ->method('convert')
                ->with($amount)
                ->will($this->returnValue($result));
        }
        $this->assertEquals($result, $this->model->currency($amount, $format, $includeContainer));
    }

    public function currencyDataProvider()
    {
        return array(
            array('amount' => '100', 'format' => true, 'includeContainer' => true, 'result' => '100grn.'),
            array('amount' => '115', 'format' => true, 'includeContainer' => false, 'result' => '1150'),
            array('amount' => '120', 'format' => false, 'includeContainer' => null, 'result' => '1200'),
        );
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
            $this->priceCurrency->expects($this->once())
                ->method('convertAndFormat')
                ->with($amount, $includeContainer, PriceCurrencyInterface::DEFAULT_PRECISION, $store)
                ->will($this->returnValue($result));
        } else {
            $this->priceCurrency->expects($this->once())
                ->method('convert')
                ->with($amount, $store)
                ->will($this->returnValue($result));
        }
        $this->assertEquals($result, $this->model->currencyByStore($amount, $store, $format, $includeContainer));
    }

    public function currencyByStoreDataProvider()
    {
        return array(
            array('amount' => '10', 'store' => 1, 'format' => true, 'includeContainer' => true, 'result' => '10grn.'),
            array('amount' => '115', 'store' => 4,  'format' => true, 'includeContainer' => false, 'result' => '1150'),
            array('amount' => '120', 'store' => 5,  'format' => false, 'includeContainer' => null, 'result' => '1200'),
        );
    }

    public function testFormatCurrency()
    {
        $amount = '120';
        $includeContainer = false;
        $result = '10grn.';

        $this->priceCurrency->expects($this->once())
            ->method('convertAndFormat')
            ->with($amount, $includeContainer)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->formatCurrency($amount, $includeContainer));
    }

    public function testFormatPrice()
    {
        $amount = '120';
        $includeContainer = false;
        $result = '10grn.';

        $this->priceCurrency->expects($this->once())
            ->method('format')
            ->with($amount, $includeContainer)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->model->formatPrice($amount, $includeContainer));
    }
}
