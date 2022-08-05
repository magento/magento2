<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model;

use InvalidArgumentException;
use Magento\AdvancedSearch\Model\SuggestedQueries;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\EngineResolver;
use Magento\Search\Model\QueryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\AdvancedSearch\Model\SuggestedQueries
 */
class SuggestedQueriesTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var SuggestedQueries;
     */
    private $model;

    /**
     * @var EngineResolverInterface|MockObject
     */
    private $engineResolverMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->engineResolverMock = $this->getMockBuilder(EngineResolver::class)
            ->setMethods(['getCurrentSearchEngine'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->engineResolverMock->expects($this->any())
            ->method('getCurrentSearchEngine')
            ->willReturn('my_engine');

        /** @var SuggestedQueriesInterface|MockObject $suggestedQueriesMock */
        $suggestedQueriesMock = $this->getMockForAbstractClass(SuggestedQueriesInterface::class);
        $suggestedQueriesMock->expects($this->any())
            ->method('isResultsCountEnabled')
            ->willReturn(true);
        $suggestedQueriesMock->expects($this->any())
            ->method('getItems')
            ->willReturn([]);
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with('search_engine')
            ->willReturn($suggestedQueriesMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            SuggestedQueries::class,
            [
                'engineResolver' => $this->engineResolverMock,
                'objectManager' => $this->objectManagerMock,
                'data' => ['my_engine' => 'search_engine']
            ]
        );
    }

    /**
     * Test isResultsCountEnabled method.
     *
     * @return void
     */
    public function testIsResultsCountEnabled(): void
    {
        $result = $this->model->isResultsCountEnabled();
        $this->assertTrue($result);
    }

    /**
     * Test isResultsCountEnabled() method failure.
     *
     * @return void
     */
    public function testIsResultsCountEnabledException(): void
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $objectManagerHelper = new ObjectManagerHelper($this);
        /* @var SuggestedQueries $model */
        $model = $objectManagerHelper->getObject(
            SuggestedQueries::class,
            [
                'engineResolver' => $this->engineResolverMock,
                'objectManager' => $objectManagerMock,
                'data' => ['my_engine' => 'search_engine']
            ]
        );
        $this->expectException(InvalidArgumentException::class);
        $model->isResultsCountEnabled();
    }

    /**
     * Test testGetItems() method.
     *
     * @return void
     */
    public function testGetItems(): void
    {
        /** @var QueryInterface|MockObject $queryInterfaceMock */
        $queryInterfaceMock = $this->getMockForAbstractClass(QueryInterface::class);
        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals([], $result);
    }
}
