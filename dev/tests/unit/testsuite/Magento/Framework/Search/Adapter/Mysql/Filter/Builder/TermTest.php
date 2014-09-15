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

namespace Magento\Framework\Search\Adapter\Mysql\Filter\Builder;

use Magento\TestFramework\Helper\ObjectManager;

class TermTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;
    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resource;
    /**
     * @var \Magento\Framework\Search\Request\Filter\Term|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestFilter;
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term
     */
    private $filter;

    /**
     * Set up
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->requestFilter = $this->getMockBuilder('Magento\Framework\Search\Request\Filter\Term')
            ->setMethods(['getField', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapter = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')
            ->setMethods(['quote'])
            ->getMockForAbstractClass();

        $this->resource = $this->getMockBuilder('Magento\Framework\App\Resource')
            ->setMethods(['getConnection'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->resource->expects($this->once())
            ->method('getConnection')
            ->with(\Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE)
            ->will($this->returnValue($this->adapter));

        $this->filter = $objectManager->getObject(
            'Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term',
            [
                'resource' => $this->resource,
            ]
        );
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $expectedResult
     * @dataProvider buildQueryDataProvider
     */
    public function testBuildQuery($field, $value, $expectedResult)
    {
        $this->requestFilter->expects($this->once())
            ->method('getField')
            ->will($this->returnValue($field));
        $this->requestFilter->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($value));
        $this->adapter->expects($this->once())
            ->method('quote')
            ->will($this->returnArgument(0));

        $actualResult = $this->filter->buildFilter($this->requestFilter);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for BuildQuery
     * @return array
     */
    public function buildQueryDataProvider()
    {
        return [
            [
                'field' => 'testField',
                'value' => 'testValue',
                'expectedResult' => 'testField = testValue',
            ],
            [
                'field' => 'testField2',
                'value' => 'testValue2',
                'expectedResult' => 'testField2 = testValue2',
            ],
        ];
    }
}
