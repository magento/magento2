<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Search\Model\EngineResolver;
use Magento\Framework\ObjectManagerInterface;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AdvancedSearch\Model\SuggestedQueries;
     */
    protected $model;

    /**
     * @var EngineResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $engineResolverMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
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
         *     \PHPUnit_Framework_MockObject_MockObject
         */
        $suggestedQueriesMock = $this->getMock(\Magento\AdvancedSearch\Model\SuggestedQueriesInterface::class);
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
     * @expectedException \InvalidArgumentException
     *
     * @return void
     */
    public function testIsResultsCountEnabledException()
    {
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
        $queryInterfaceMock = $this->getMock(\Magento\Search\Model\QueryInterface::class);
        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals([], $result);
    }
}
