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

namespace Magento\Sales\Helper\Quote\Item;

/**
 * Class CompareTest
 */
class CompareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Helper\Quote\Item\Compare
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \Magento\Sales\Model\Quote\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $comparedMock;

    /**
     * @var \Magento\Sales\Model\Quote\Item\Option|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionMock;

    /**
     * test setUp
     */
    public function setUp()
    {
        $this->itemMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            ['__wakeup', 'getProductId', 'getOptions'],
            [],
            '',
            false
        );
        $this->comparedMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item',
            ['__wakeup', 'getProductId', 'getOptions'],
            [],
            '',
            false
        );
        $this->optionMock = $this->getMock(
            'Magento\Sales\Model\Quote\Item\Option',
            ['__wakeup', 'getCode', 'getValue'],
            [],
            '',
            false
        );

        $this->helper = new \Magento\Sales\Helper\Quote\Item\Compare();
    }

    /**
     * @param string $code
     * @param mixed $value
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getOptionMock($code, $value)
    {
        $optionMock = clone $this->optionMock;
        $optionMock->expects($this->once())
            ->method('getCode')
            ->will($this->returnValue($code));
        $optionMock->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));
        return $optionMock;
    }

    /**
     * test compare two different products
     */
    public function testCompareDifferentProduct()
    {
        $this->itemMock->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->itemMock->expects($this->once())
            ->method('getProductId')
            ->will($this->returnValue(2));

        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items with different options
     */
    public function testCompareProductWithDifferentOptions()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));

        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', serialize([
                            'value' => 'value-1',
                            'qty' => 2
                        ])
                    )]
            ));
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-4', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', serialize([
                        'value' => 'value-1',
                        'qty' => 2
                    ]))
                ])
            );
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first with options and second without options
     */
    public function testCompareItemWithComparedWithoutOption()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', serialize([
                            'value' => 'value-1',
                            'qty' => 2
                        ])
                    )]
            ));
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }

    /**
     * test compare two items first without options and second with options
     */
    public function testCompareItemWithoutOptionWithCompared()
    {
        $this->itemMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getProductId')
            ->will($this->returnValue(1));
        $this->comparedMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([
                    $this->getOptionMock('option-1', 1),
                    $this->getOptionMock('option-2', 'option-value'),
                    $this->getOptionMock('option-3', serialize([
                            'value' => 'value-1',
                            'qty' => 2
                        ])
                    )]
            ));
        $this->itemMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue([]));
        $this->assertFalse($this->helper->compare($this->itemMock, $this->comparedMock));
    }
}
