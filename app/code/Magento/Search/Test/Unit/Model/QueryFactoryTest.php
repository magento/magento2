<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Helper\Data;
use Magento\Search\Model\Query;
use Magento\Search\Model\QueryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class QueryFactoryTest tests Magento\Search\Model\QueryFactory
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QueryFactoryTest extends TestCase
{
    /**
     * @var QueryFactory
     */
    private $model;

    /**
     * @var Data|MockObject
     */
    private $queryHelper;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var StringUtils|MockObject
     */
    private $string;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var Query|MockObject
     */
    private $query;

    /**
     * SetUp method
     */
    protected function setUp(): void
    {
        $this->queryHelper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->string = $this->getMockBuilder(StringUtils::class)
            ->onlyMethods(['substr', 'strlen', 'cleanString'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->query = $this->getMockBuilder(Query::class)
            ->addMethods(['setIsQueryTextExceeded', 'setIsQueryTextShort'])
            ->onlyMethods(['loadByQueryText', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        /** @var Context|MockObject $context */
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
            ->willReturnCallback(
                function ($arg1, $arg2) use ($data) {
                    if ($arg1 == Query::class && $arg2 == $data) {
                        return $this->query;
                    }
                }
            );

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

        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();

        $this->assertSame($this->query, $result);
        $this->assertSearchQuery($cleanedRawText);
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

        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result, 'After first execution queries are not same');

        $result = $this->model->get();
        $this->assertSame($this->query, $result, 'After second execution queries are not same');
        $this->assertSearchQuery($cleanedRawText);
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
            ->willReturnCallback(
                function ($arg1, $arg2, $arg3) use ($cleanedRawText, $maxQueryLength, $subRawText) {
                    if ($arg1 == $cleanedRawText && $arg2 == 0 && $arg3 == $maxQueryLength) {
                        return $subRawText;
                    }
                }
            );

        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($subRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
        $this->assertSearchQuery($subRawText);
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

        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
        $this->assertSearchQuery($cleanedRawText);
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

        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort);

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
        $this->assertSearchQuery($cleanedRawText);
    }

    /**
     * Test for inaccurate match of search query in query_text table
     *
     * Because of inaccurate string comparison of utf8_general_ci,
     * the search_query result text may be different from the original text (e.g organos, Organos, Órganos)
     */
    public function testInaccurateQueryTextMatch()
    {
        $queryId = 1;
        $maxQueryLength = 100;
        $minQueryLength = 3;
        $rawQueryText = 'Órganos';
        $cleanedRawText = 'Órganos';
        $isQueryTextExceeded = false;
        $isQueryTextShort = false;

        $this->mockString($cleanedRawText);
        $this->mockQueryLengths($maxQueryLength, $minQueryLength);
        $this->mockGetRawQueryText($rawQueryText);
        $this->mockSimpleQuery($cleanedRawText, $queryId, $isQueryTextExceeded, $isQueryTextShort, 'Organos');

        $this->mockCreateQuery();

        $result = $this->model->get();
        $this->assertSame($this->query, $result);
        $this->assertSearchQuery($cleanedRawText);
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
            ->willReturnCallback(
                function ($arg) use ($rawQueryText) {
                    if ($arg == QueryFactory::QUERY_VAR_NAME) {
                        return $rawQueryText;
                    }
                }
            );
    }

    /**
     * @param string $cleanedRawText
     * @return void
     */
    private function mockString($cleanedRawText)
    {
        $this->string->expects($this->any())
            ->method('cleanString')
            ->willReturnCallback(
                function ($arg) use ($cleanedRawText) {
                    if ($arg == $cleanedRawText) {
                        return $arg;
                    }
                }
            );
        $this->string->expects($this->any())
            ->method('strlen')
            ->willReturnCallback(
                function ($arg) use ($cleanedRawText) {
                    if ($arg == $cleanedRawText) {
                        return strlen($cleanedRawText);
                    }
                }
            );
    }

    /**
     * @return void
     */
    private function mockCreateQuery()
    {
        $this->objectManager->expects($this->once())
            ->method('create')
            ->willReturnCallback(
                function ($arg1, $arg2) {
                    if ($arg1 == Query::class && empty($arg2)) {
                        return $this->query;
                    }
                }
            );
    }

    /**
     * @param string $cleanedRawText
     * @param int $queryId
     * @param bool $isQueryTextExceeded
     * @param bool $isQueryTextShort
     * @param string $matchedQueryText
     * @return void
     */
    private function mockSimpleQuery(
        string $cleanedRawText,
        ?int $queryId,
        bool $isQueryTextExceeded,
        bool $isQueryTextShort,
        ?string $matchedQueryText = null
    ) {
        if (null === $matchedQueryText) {
            $matchedQueryText = $cleanedRawText;
        }
        $this->query->expects($this->once())
            ->method('loadByQueryText')
            ->willReturnCallback(
                function ($arg) use ($cleanedRawText) {
                    if ($arg == $cleanedRawText) {
                        return $this->query;
                    }
                }
            );
        $this->query->setData(['query_text' => $matchedQueryText]);
        $this->query->expects($this->any())
            ->method('getId')
            ->willReturn($queryId);
        $this->query->expects($this->once())
            ->method('setIsQueryTextExceeded')
            ->willReturnCallback(
                function ($arg) use ($isQueryTextExceeded) {
                    if ($arg == $isQueryTextExceeded) {
                        return null;
                    }
                }
            );
        $this->query->expects($this->once())
            ->method('setIsQueryTextShort')
            ->willReturnCallback(
                function ($arg) use ($isQueryTextShort) {
                    if ($arg == $isQueryTextShort) {
                        return null;
                    }
                }
            );
    }

    /**
     * @param string $cleanedRawText
     * @return void
     */
    private function assertSearchQuery($cleanedRawText)
    {
        $this->assertEquals($cleanedRawText, $this->query->getQueryText());
    }
}
