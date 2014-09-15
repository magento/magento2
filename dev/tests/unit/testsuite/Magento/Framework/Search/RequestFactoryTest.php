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
namespace Magento\Framework\Search;

use Magento\TestFramework\Helper\ObjectManager;

class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\RequestFactory
     */
    private $factory;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Search\Request\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->objectManager = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->setMethods(['create', 'get', 'configure'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->config = $this->getMockBuilder('Magento\Framework\Search\Request\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = $helper->getObject(
            'Magento\Framework\Search\RequestFactory',
            [
                'objectManager' => $this->objectManager,
                'config' => $this->config
            ]
        );
    }

    public function testCreate()
    {
        $requestName = 'request';
        $bindValues = [':str' => 'rpl'];
        $configData = [
            'queries' => ':str',
            'filters' => 'f',
            'query' => 'q',
            'aggregations' => 'a',
            'index' => 'i',
            'from' => '1',
            'size' => '15',
            'dimensions' => [
                'name' => ['name' => '', 'value' => '']
            ]
        ];
        $mappedQuery = $configData['query'] . 'Mapped';
        $this->config->expects($this->once())->method('get')->with($this->equalTo($requestName))
            ->will($this->returnValue($configData));

        /** @var \Magento\Framework\Search\Request\Mapper|\PHPUnit_Framework_MockObject_MockObject $mapper */
        $mapper = $this->getMockBuilder('Magento\Framework\Search\Request\Mapper')
            ->setMethods(['getRootQuery', 'getBuckets'])
            ->disableOriginalConstructor()
            ->getMock();

        /** @var \Magento\Framework\Search\Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Magento\Framework\Search\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->at(0))->method('create')
            ->with(
                $this->equalTo('Magento\Framework\Search\Request\Mapper'),
                $this->equalTo(
                    [
                        'objectManager' => $this->objectManager,
                        'queries' => $bindValues[':str'],
                        'rootQueryName' => $configData['query'],
                        'aggregations' => $configData['aggregations'],
                        'filters' => $configData['filters']
                    ]
                )
            )
            ->will($this->returnValue($mapper));

        /** @var \Magento\Framework\Search\Request\Dimension|\PHPUnit_Framework_MockObject_MockObject $dimension */
        $dimension = $this->getMockBuilder('Magento\Framework\Search\Request\Dimension')
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->expects($this->at(1))->method('create')
            ->with(
                $this->equalTo('Magento\Framework\Search\Request\Dimension'),
                $this->equalTo(
                    [
                        'name' => '',
                        'value' => '',
                    ]
                )
            )
            ->will($this->returnValue($dimension));

        $this->objectManager->expects($this->at(2))->method('create')
            ->with(
                $this->equalTo('Magento\Framework\Search\Request'),
                $this->equalTo(
                    [
                        'name' => $configData['query'],
                        'indexName' => $configData['index'],
                        'from' => $configData['from'],
                        'size' => $configData['size'],
                        'query' => $mappedQuery,
                        'dimensions' => [
                            'name' => $dimension
                        ],
                        'buckets' => [],
                    ]
                )
            )
            ->will($this->returnValue($request));

        $mapper->expects($this->once())->method('getRootQuery')
            ->will($this->returnValue($mappedQuery));
        $mapper->expects($this->once())->method('getBuckets')->will($this->returnValue([]));

        $this->assertEquals($request, $this->factory->create($requestName, $bindValues));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateInvalidArgumentException()
    {
        $requestName = 'rn';
        $this->config->expects($this->once())->method('get')->with($this->equalTo($requestName))
            ->will($this->returnValue(null));

        $this->factory->create($requestName);
    }
}
