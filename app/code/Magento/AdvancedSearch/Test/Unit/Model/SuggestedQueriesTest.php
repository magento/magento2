<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Test\Unit\Model;

use PHPUnit\Framework\TestCase;
use Magento\AdvancedSearch\Model\SuggestedQueries;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Search\Model\EngineResolver;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Search\Model\QueryInterface;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\ObjectManagerInterface;

class SuggestedQueriesTest extends TestCase
{
    /**
     * @var SuggestedQueries ;
     */
    protected $model;

    /**
     * @var EngineResolverInterface|MockObject
     */
    protected $engineResolverMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

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
        $this->engineResolverMock
            ->method('getCurrentSearchEngine')
            ->willReturn('my_engine');

        /**
         * @var \Magento\AdvancedSearch\Model\SuggestedQueriesInterface|
         *     \PHPUnit_Framework_MockObject_MockObject
         */
        $suggestedQueriesMock = $this->createMock(SuggestedQueriesInterface::class);
        $suggestedQueriesMock
            ->method('isResultsCountEnabled')
            ->willReturn(true);
        $suggestedQueriesMock
            ->method('getItems')
            ->willReturn([]);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->objectManagerMock
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
    public function testIsResultsCountEnabled()
    {
        $result = $this->model->isResultsCountEnabled();
        $this->assertTrue($result);
    }

    /**
     * Test isResultsCountEnabled() method failure.
     *
     * @return void
     */
    public function testIsResultsCountEnabledException()
    {
        $this->expectException('InvalidArgumentException');
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $objectManagerHelper = new ObjectManagerHelper($this);
        /* @var $model \Magento\AdvancedSearch\Model\SuggestedQueries */
        $model = $objectManagerHelper->getObject(
            SuggestedQueries::class,
            [
                'engineResolver' => $this->engineResolverMock,
                'objectManager' => $objectManagerMock,
                'data' => ['my_engine' => 'search_engine']
            ]
        );
        $model->isResultsCountEnabled();
    }

    /**
     * Test testGetItems() method.
     *
     * @return void
     */
    public function testGetItems()
    {
        /** @var $queryInterfaceMock \Magento\Search\Model\QueryInterface */
        $queryInterfaceMock = $this->createMock(QueryInterface::class);
        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals([], $result);
    }
}
