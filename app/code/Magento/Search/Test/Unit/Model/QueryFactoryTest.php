<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    const XML_PATH_MAX_QUERY_LENGTH = 'catalog/search/max_query_length';

    const QUERY_VAR_NAME = 'q';

    /** @var  \Magento\Search\Model\Query|\PHPUnit_Framework_MockObject_MockObject */
    protected $queryMock;

    /** @var  \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $requestMock;

    /** @var \Magento\Search\Model\QueryFactory */
    protected $queryFactory;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\App\Helper\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject */
    protected $stringMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder('\Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Helper\Context')
            ->setMethods(['getRequest', 'getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->setMethods([])
            ->getMock();
        $this->stringMock = $this->getMockBuilder('Magento\Framework\Stdlib\StringUtils')
            ->setMethods(['cleanString', 'substr'])
            ->getMock();
        $this->stringMock->expects($this->any())
            ->method('cleanString')
            ->will($this->returnArgument(0));
        $this->scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->setMethods([])
            ->getMock();
        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->will($this->returnValue($this->scopeConfigMock));
        $this->queryMock = $this->getMockBuilder('\Magento\Search\Model\Query')
            ->disableOriginalConstructor()
            ->setMethods(['setIsQueryTextExceeded', 'getId', 'setQueryText', 'loadByQuery'])
            ->getMock();
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->queryFactory = $this->objectManagerHelper->getObject(
            'Magento\Search\Model\QueryFactory',
            [
                'context' => $this->contextMock,
                'objectManager' => $this->objectManagerMock,
                'string' => $this->stringMock,
            ]
        );
    }

    public function testGetNewQuery()
    {
        $queryId = null;

        $this->mapScopeConfig(
            [
                self::XML_PATH_MAX_QUERY_LENGTH => 120,
            ]
        );
        $rawQueryText = 'Simple product';
        $preparedQueryText = $rawQueryText;
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo(self::QUERY_VAR_NAME))
            ->will($this->returnValue($rawQueryText));

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Search\Model\Query'))
            ->will($this->returnValue($this->queryMock));
        $this->queryMock->expects($this->once())
            ->method('loadByQuery')
            ->with($this->equalTo($preparedQueryText))
            ->will($this->returnSelf());
        $this->queryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($queryId));
        $this->queryMock->expects($this->once())
            ->method('setQueryText')
            ->with($preparedQueryText)
            ->will($this->returnSelf());
        $this->queryMock->expects($this->once())
            ->method('setIsQueryTextExceeded')
            ->with($this->equalTo(false))
            ->will($this->returnSelf());
        $query = $this->queryFactory->get();
        $this->assertSame($this->queryMock, $query);
    }

    public function testGetLoadedQuery()
    {
        $queryId = 123;

        $this->mapScopeConfig(
            [
                self::XML_PATH_MAX_QUERY_LENGTH => 20,
            ]
        );
        $rawQueryText = 'Simple product';
        $preparedQueryText = $rawQueryText;
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo(self::QUERY_VAR_NAME))
            ->will($this->returnValue($rawQueryText));

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Search\Model\Query'))
            ->will($this->returnValue($this->queryMock));
        $this->queryMock->expects($this->once())
            ->method('loadByQuery')
            ->with($this->equalTo($preparedQueryText))
            ->will($this->returnSelf());
        $this->queryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($queryId));
        $this->queryMock->expects($this->once())
            ->method('setIsQueryTextExceeded')
            ->with($this->equalTo(false))
            ->will($this->returnSelf());
        $query = $this->queryFactory->get();
        $this->assertSame($this->queryMock, $query);
    }

    public function testGetTooLongQuery()
    {
        $queryId = 123;

        $this->mapScopeConfig(
            [
                self::XML_PATH_MAX_QUERY_LENGTH => 12,
            ]
        );
        $rawQueryText = 'This is very long search query text';
        $preparedQueryText = 'This is very';
        $this->stringMock->expects($this->once())
            ->method('substr')
            ->with($this->equalTo($rawQueryText))
            ->will($this->returnValue($preparedQueryText));
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo(self::QUERY_VAR_NAME))
            ->will($this->returnValue($rawQueryText));
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Search\Model\Query'))
            ->will($this->returnValue($this->queryMock));
        $this->queryMock->expects($this->once())
            ->method('loadByQuery')
            ->with($this->equalTo($preparedQueryText))
            ->will($this->returnSelf());
        $this->queryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($queryId));
        $this->queryMock->expects($this->once())
            ->method('setIsQueryTextExceeded')
            ->with($this->equalTo(true))
            ->will($this->returnSelf());
        $query = $this->queryFactory->get();
        $this->assertSame($this->queryMock, $query);
    }

    /**
     * @depends testGetNewQuery
     * @param $query
     */
    public function testGetQueryTwice($query)
    {
        $queryId = null;

        $this->mapScopeConfig(
            [
                self::XML_PATH_MAX_QUERY_LENGTH => 120,
            ]
        );
        $rawQueryText = 'Simple product';
        $preparedQueryText = $rawQueryText;
        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo(self::QUERY_VAR_NAME))
            ->will($this->returnValue($rawQueryText));

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Search\Model\Query'))
            ->will($this->returnValue($this->queryMock));
        $this->queryMock->expects($this->once())
            ->method('loadByQuery')
            ->with($this->equalTo($preparedQueryText))
            ->will($this->returnSelf());
        $this->queryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($queryId));
        $this->queryMock->expects($this->once())
            ->method('setQueryText')
            ->with($preparedQueryText)
            ->will($this->returnSelf());
        $this->queryMock->expects($this->once())
            ->method('setIsQueryTextExceeded')
            ->with($this->equalTo(false))
            ->will($this->returnSelf());
        $query = $this->queryFactory->get();
        $this->assertSame($this->queryMock, $query);
        $this->assertSame($query, $this->queryFactory->get());
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Search\Model\Query'))
            ->will($this->returnValue($this->queryMock));
        $query = $this->queryFactory->create();
        $this->assertSame($this->queryMock, $query);
    }

    /**
     * @param array $map
     */
    private function mapScopeConfig(array $map)
    {
        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->will(
                $this->returnCallback(
                    function ($path) use ($map) {
                        return isset($map[$path]) ? $map[$path] : null;
                    }
                )
            );
    }
}
