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
namespace Magento\Tax\Model\Sales\Total\Quote;

/**
 * Test class for \Magento\Tax\Model\Sales\Total\Quote\Tax
 */
use Magento\Tax\Model\Calculation;
use Magento\TestFramework\Helper\ObjectManager;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector;

class CommonTaxCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector
     */
    private $commonTaxCollector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Tax\Model\Config
     */
    private $taxConfig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote\Address
     */
    private $address;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Sales\Model\Quote
     */
    private $quote;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Store\Model\Store
     */
    private $store;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->taxConfig = $this->getMockBuilder('\Magento\Tax\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['getShippingTaxClass', 'shippingPriceIncludesTax'])
            ->getMock();

        $this->store = $this->getMockBuilder('\Magento\Store\Model\Store')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $this->quote = $this->getMockBuilder('\Magento\Sales\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getStore'])
            ->getMock();

        $this->quote->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($this->store));

        $this->address = $this->getMockBuilder('\Magento\Sales\Model\Quote\Address')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup', 'getQuote'])
            ->getMock();

        $this->address->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));

        $this->commonTaxCollector = $objectManager->getObject(
            'Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector',
            ['taxConfig' => $this->taxConfig]
        );
    }

    /**
     * @param array $addressData
     * @param bool $useBaseCurrency
     * @param string $shippingTaxClass
     * @param bool shippingPriceInclTax
     * @param array $expectedValue
     * @dataProvider getShippingDataObjectDataProvider
     */
    public function testGetShippingDataObject(
        array $addressData,
        $useBaseCurrency,
        $shippingTaxClass,
        $shippingPriceInclTax,
        array $expectedValue
    ) {
        $this->taxConfig->expects($this->any())
            ->method('getShippingTaxClass')
            ->with($this->store)
            ->will($this->returnValue($shippingTaxClass));
        $this->taxConfig->expects($this->any())
            ->method('shippingPriceIncludesTax')
            ->with($this->store)
            ->will($this->returnValue($shippingPriceInclTax));

        foreach ($addressData as $key => $value) {
            $this->address->setData($key, $value);
        }

        $shippingDataObject = $this->commonTaxCollector->getShippingDataObject($this->address, $useBaseCurrency);
        $this->assertEquals($expectedValue, $shippingDataObject->__toArray());

        //call it again, make sure we get the same output
        $shippingDataObject = $this->commonTaxCollector->getShippingDataObject($this->address, $useBaseCurrency);
        $this->assertEquals($expectedValue, $shippingDataObject->__toArray());
    }

    public function getShippingDataObjectDataProvider()
    {
        $data = [
            'free_shipping' => [
                'address' =>
                    [
                        'shipping_amount' => 0,
                        'base_shipping_amount' => 0,
                    ],
                'use_base_currency' => false,
                'shipping_tax_class' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
                'expected_value' => [
                    'type' => CommonTaxCollector::ITEM_TYPE_SHIPPING,
                    'code' => CommonTaxCollector::ITEM_CODE_SHIPPING,
                    'quantity' => 1,
                    'unit_price' => 0,
                    'tax_class_key' => [
                        'type' => 'id',
                        'value' => 'shippingTaxClass',
                    ],
                    'tax_included' => true,
                ]
            ],
            'none_zero_none_base' => [
                'address' => [
                        'shipping_amount' => 10,
                        'base_shipping_amount' => 5,
                    ],
                'use_base_currency' => false,
                'shipping_tax_class' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
                'expected_value' => [
                    'type' => CommonTaxCollector::ITEM_TYPE_SHIPPING,
                    'code' => CommonTaxCollector::ITEM_CODE_SHIPPING,
                    'quantity' => 1,
                    'unit_price' => 10,
                    'tax_class_key' => [
                        'type' => 'id',
                        'value' => 'shippingTaxClass',
                    ],
                    'tax_included' => true,
                ]
            ],
            'none_zero_base' => [
                'address' => [
                    'shipping_amount' => 10,
                    'base_shipping_amount' => 5,
                ],
                'use_base_currency' => true,
                'shipping_tax_class' => 'shippingTaxClass',
                'shippingPriceInclTax' => true,
                'expected_value' => [
                    'type' => CommonTaxCollector::ITEM_TYPE_SHIPPING,
                    'code' => CommonTaxCollector::ITEM_CODE_SHIPPING,
                    'quantity' => 1,
                    'unit_price' => 5,
                    'tax_class_key' => [
                        'type' => 'id',
                        'value' => 'shippingTaxClass',
                    ],
                    'tax_included' => true,
                ]
            ],
        ];

        return $data;
    }
}
