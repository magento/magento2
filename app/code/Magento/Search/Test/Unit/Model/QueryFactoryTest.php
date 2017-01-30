<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Test\Unit\Model;

use Magento\Search\Helper\Data;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Search\Model\Query;

class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryFactory
     */
    private $model;

    /**
     * @var Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $queryHelper;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $string;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var Query|\PHPUnit_Framework_MockObject_MockObject
     */
    private $query;

    /**
     * SetUp method
     */
    protected function setUp()
    {
        $this->queryHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->string = $this->getMockBuilder(StringUtils::class)
            ->setMethods(['substr', 'strlen', 'cleanString'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->query = $this->getMockBuilder(Query::class)
            ->setMethods(['setIsQueryTextExceeded', 'setIsQueryTextShort', 'loadByQueryText', 'getId', 'setQueryText'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $context */
        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);

        $this->model = (new ObjectManager($this))->getObject(
            QueryFactory::class,
            [
                'queryHelper' => $this->queryHelper,
                'context' => $context,
                'string' => $this->string,
                'objectManager' => $this->objectManager
            ]
        );
    }

    /**
     * Test for create method
     */
    public function testCreate()
    {
        $data = [1, 2, 3];

        $this->objectManager->expects($this->once())
            ->method('create')
            ->withConsecutive([Query::class, $data])
            ->willReturn($this->query);

        $result = $this->model->create($data);

        $this->assertSame($this->query, $result);
    }

    /**
     * Test for get new query method
     */
    public function testGetNewQuery()
    {
        $queryId = 123;
        $maxQueryLength = 100;
        $minQueryLength = 3;
        $rawQueryText = '  Simple product   ';
        $cleanedRawText = 'Simple product';
        $isQueryTextExceeded = false;
        $isQueryTextShort = false;

        $this->mockSetQueryTextNeverExecute($cleanedRawText);
        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();

        $this->assertSame($this->query, $result);
    }

    /**
     * Test for get query twice method
     */
    public function testGetQueryTwice()
    {
        $queryId = 123;
        $maxQueryLength = 100;
        $minQueryLength = 3;
        $rawQueryText = '  Simple product   ';
        $cleanedRawText = 'Simple product';
        $isQueryTextExceeded = false;
        $isQueryTextShort = false;

        $this->mockSetQueryTextNeverExecute($cleanedRawText);
        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result, 'After first execution queries are not same');

        $result = $this->model->get();
        $this->assertSame($this->query, $result, 'After second execution queries are not same');
    }

    /**
     * Test for get query is too long method
     */
    public function testGetTooLongQuery()
    {
        $queryId = 123;
        $maxQueryLength = 8;
        $minQueryLength = 3;
        $rawQueryText = '  Simple product   ';
        $cleanedRawText = 'Simple product';
        $subRawText = 'Simple p';
        $isQueryTextExceeded = true;
        $isQueryTextShort = false;

        $this->string->expects($this->any())
            ->method('substr')
            ->withConsecutive([$cleanedRawText, 0, $maxQueryLength])
            ->willReturn($subRawText);

        $this->mockSetQueryTextNeverExecute($cleanedRawText);
        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($subRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
    }

    /**
     * Test for get query is Short long method
     */
    public function testGetTooShortQuery()
    {
        $queryId = 123;
        $maxQueryLength = 800;
        $minQueryLength = 500;
        $rawQueryText = '  Simple product   ';
        $cleanedRawText = 'Simple product';
        $isQueryTextExceeded = false;
        $isQueryTextShort = true;

        $this->mockSetQueryTextNeverExecute($cleanedRawText);
        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
    }

    /**
     * Test for get query is Short long method
     */
    public function testGetQueryWithoutId()
    {
        $queryId = 0;
        $maxQueryLength = 100;
        $minQueryLength = 3;
        $rawQueryText = '  Simple product   ';
        $cleanedRawText = 'Simple product';
        $isQueryTextExceeded = false;
        $isQueryTextShort = false;

        $this->mockSetQueryTextOnceExecute($cleanedRawText);
        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
    }

    /**
     * @param int $maxQueryLength
     * @param int $minQueryLength
     * @return void
     */
    private function mockQueryLengths($maxQueryLength, $minQueryLength)
    {
        $this->queryHelper->expects($this->once())
            ->method('getMaxQueryLength')
            ->willReturn($maxQueryLength);
        $this->queryHelper->expects($this->once())
            ->method('getMinQueryLength')
            ->willReturn($minQueryLength);
    }

    /**
     * @param string $rawQueryText
     * @return void
     */
    private function mockGetRawQueryText($rawQueryText)
    {
        $this->request->expects($this->any())
            ->method('getParam')
            ->withConsecutive([QueryFactory::QUERY_VAR_NAME])
            ->willReturn($rawQueryText);
    }

    /**
     * @param string $cleanedRawText
     * @return void
     */
    private function mockString($cleanedRawText)
    {
        $this->string->expects($this->any())
            ->method('cleanString')
            ->withConsecutive([$cleanedRawText])
            ->willReturnArgument(0);
        $this->string->expects($this->any())
            ->method('strlen')
            ->withConsecutive([$cleanedRawText])
            ->willReturn(strlen($cleanedRawText));
    }

    /**
     * @return void
     */
    private function mockCreateQuery()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->withConsecutive([Query::class, []])
            ->willReturn($this->query);
    }

    /**
     * @param string $cleanedRawText
     * @param int $queryId
     * @param bool $isQueryTextExceeded
     * @param bool $isQueryTextShort
     * @return void
     */
    private function mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort)
    {
        $this->query->expects($this->once())
            ->method('loadByQueryText')
            ->withConsecutive([$cleanedRawText])
            ->willReturnSelf();
        $this->query->expects($this->once())
            ->method('getId')
            ->willReturn($queryId);
        $this->query->expects($this->once())
            ->method('setIsQueryTextExceeded')
            ->withConsecutive([$isQueryTextExceeded]);
        $this->query->expects($this->once())
            ->method('setIsQueryTextShort')
            ->withConsecutive([$isQueryTextShort]);
    }

    /**
     * @param string $cleanedRawText
     * @return void
     */
    private function mockSetQueryTextNeverExecute($cleanedRawText)
    {
        $this->query->expects($this->never())
            ->method('setQueryText')
            ->withConsecutive([$cleanedRawText])
            ->willReturnSelf();
    }

    /**
     * @param string $cleanedRawText
     * @param void
     */
    private function mockSetQueryTextOnceExecute($cleanedRawText)
    {
        $this->query->expects($this->once())
            ->method('setQueryText')
            ->withConsecutive([$cleanedRawText])
            ->willReturnSelf();
    }
}
