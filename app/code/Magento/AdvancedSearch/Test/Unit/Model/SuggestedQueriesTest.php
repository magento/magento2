<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Test\Unit\Model;

use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\ObjectManagerInterface;

class SuggestedQueriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\AdvancedSearch\Model\SuggestedQueries;
     */
    protected $model;

    /**
     * @var EngineResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $engineResolverMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit\Framework\MockObject\MockObject
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
        $this->engineResolverMock = $this->getMockBuilder(\Magento\Search\Model\EngineResolver::class)
            ->setMethods(['getCurrentSearchEngine'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->engineResolverMock->expects($this->any())
            ->method('getCurrentSearchEngine')
            ->willReturn('my_engine');

        /**
         * @var \Magento\AdvancedSearch\Model\SuggestedQueriesInterface|
         *     \PHPUnit\Framework\MockObject\MockObject
         */
        $suggestedQueriesMock = $this->createMock(\Magento\AdvancedSearch\Model\SuggestedQueriesInterface::class);
        $suggestedQueriesMock->expects($this->any())
            ->method('isResultsCountEnabled')
            ->willReturn(true);
        $suggestedQueriesMock->expects($this->any())
            ->method('getItems')
            ->willReturn([]);

        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with('search_engine')
            ->willReturn($suggestedQueriesMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\AdvancedSearch\Model\SuggestedQueries::class,
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
        $this->expectException(\InvalidArgumentException::class);

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $objectManagerHelper = new ObjectManagerHelper($this);
        /* @var $model \Magento\AdvancedSearch\Model\SuggestedQueries */
        $model = $objectManagerHelper->getObject(
            \Magento\AdvancedSearch\Model\SuggestedQueries::class,
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
        $queryInterfaceMock = $this->createMock(\Magento\Search\Model\QueryInterface::class);
        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals([], $result);
    }
}
