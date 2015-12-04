<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AdvancedSearch\Model\SuggestedQueries;
     */
    protected $model;

    /**
     * @var \Magento\Search\Model\EngineResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $engineResolverMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
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
        $this->engineResolverMock = $this->getMockBuilder('Magento\Search\Model\EngineResolver')
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
        $suggestedQueriesInterfaceMock = $this->getMock(
            'Magento\AdvancedSearch\Model\SuggestedQueriesInterface'
        );
        $suggestedQueriesInterfaceMock->expects($this->any())
            ->method('isResultsCountEnabled')
            ->willReturn(true);
        $suggestedQueriesInterfaceMock->expects($this->any())
            ->method('getItems')
            ->willReturn([]);

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->any())
            ->method('create')
            ->with('search_engine')
            ->willReturn($suggestedQueriesInterfaceMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            '\Magento\AdvancedSearch\Model\SuggestedQueries',
            [
                'engineResolver' => $this->engineResolverMock,
                'objectManager' => $this->objectManagerMock,
                'data' => ['my_engine' => 'search_engine']
            ]
        );
    }

    /**
     * Test isResultsCountEnabled method.
     */
    public function testIsResultsCountEnabled()
    {
        $result = $this->model->isResultsCountEnabled();
        $this->assertTrue($result);
    }

    /**
     * Test isResultsCountEnabled() method failure
     * @expectedException \InvalidArgumentException
     */
    public function testIsResultsCountEnabledException()
    {
        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn(null);

        $objectManagerHelper = new ObjectManagerHelper($this);
        /* @var $model \Magento\AdvancedSearch\Model\SuggestedQueries */
        $model = $objectManagerHelper->getObject(
            '\Magento\AdvancedSearch\Model\SuggestedQueries',
            [
                'engineResolver' => $this->engineResolverMock,
                'objectManager' => $objectManagerMock,
                'data' => ['my_engine' => 'search_engine']
            ]
        );
        $model->isResultsCountEnabled();
    }

    public function testGetItems()
    {
        $queryInterfaceMock = $this->getMock('Magento\Search\Model\QueryInterface');
        $result = $this->model->getItems($queryInterfaceMock);
        $this->assertEquals([], $result);
    }
}
