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

namespace Magento\Catalog\Model\Layer\Filter\DataProvider;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal
 */
class DecimalTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \Magento\Catalog\Model\Layer\Filter\FilterInterface|MockObject */
    private $filter;

    /** @var  \Magento\Catalog\Model\Resource\Layer\Filter\Decimal|MockObject */
    private $resource;

    /**
     * @var \Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal
     */
    private $target;

    protected function setUp()
    {
        $this->filter = $this->getMockBuilder('\Magento\Catalog\Model\Layer\Filter\FilterInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMockForAbstractClass();
        $this->resource = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Layer\Filter\Decimal')
            ->disableOriginalConstructor()
            ->setMethods(['getMinMax', 'getCount'])
            ->getMock();
        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            'Magento\Catalog\Model\Layer\Filter\DataProvider\Decimal',
            [
                'resource' => $this->resource,
            ]
        );
    }

    public function testGetMaxValue()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->will($this->returnValue([10, 20]));
        $max = $this->target->getMaxValue($this->filter);
        $this->assertSame(20, $max);
    }

    public function testGetMinValue()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->will($this->returnValue([50, 220]));
        $min = $this->target->getMinValue($this->filter);
        $this->assertSame(50, $min);
    }

    public function testGetRangeItemCounts()
    {
        $range = 100500;
        $this->resource->expects($this->once())
            ->method('getCount')
            ->with($this->filter, $range)
            ->will($this->returnValue(350));
        $this->assertSame(350, $this->target->getRangeItemCounts($range, $this->filter));
    }

    public function testGetRange()
    {
        $this->resource->expects($this->once())
            ->method('getMinMax')
            ->with($this->filter)
            ->will($this->returnValue([74, 147]));
        $range = $this->target->getRange($this->filter);
        $this->assertSame(10, $range);
    }
}
