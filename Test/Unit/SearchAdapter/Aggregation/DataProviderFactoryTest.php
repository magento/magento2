<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Aggregation;

use Magento\Elasticsearch\SearchAdapter\Aggregation\DataProviderFactory;
use Magento\Elasticsearch\SearchAdapter\Dynamic\DataProvider;
use Magento\Elasticsearch\SearchAdapter\QueryContainer;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

class DataProviderFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManager;

    /**
     * @var DataProviderFactory
     */
    private $factory;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->objectManager = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->factory = $objectManager->getObject(
            DataProviderFactory::class,
            ['objectManager' => $this->objectManager]
        );
    }

    public function testCreateDataProviderWithoutQuery()
    {
        $this->objectManager->expects($this->never())->method('create');
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->getMockForAbstractClass();
        $this->assertSame($dataProvider, $this->factory->create($dataProvider));
    }

    public function testCreateDataProviderWithEmptyQuery()
    {
        $this->objectManager->expects($this->never())->method('create');
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->getMockForAbstractClass();
        $this->assertSame($dataProvider, $this->factory->create($dataProvider, null));
    }

    public function testCreateDataProviderWithQuery()
    {
        $this->objectManager->expects($this->never())->method('create');
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockBuilder(DataProviderInterface::class)
            ->getMockForAbstractClass();
        /** @var \PHPUnit_Framework_MockObject_MockObject $queryContainerMock */
        $queryContainerMock = $this->getMockBuilder(QueryContainer::class)
            ->setMethods(['getQuery'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryContainerMock->expects($this->never())->method('getQuery');
        $this->assertSame($dataProvider, $this->factory->create($dataProvider, $queryContainerMock));
    }

    public function testCreateQueryAwareDataProvider()
    {
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        /** @var \PHPUnit_Framework_MockObject_MockObject $queryContainer */
        $queryContainer = $this->getMockBuilder(QueryContainer::class)
            ->setMethods(['getQuery'])
            ->disableOriginalConstructor()
            ->getMock();
        $queryContainer->expects($this->never())->method('getQuery');
        /** @var DataProviderInterface $recreatedDataProvider */
        $recreatedDataProvider = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager->expects($this->once())
            ->method('create')
            ->with($this->isType('string'), ['queryContainer' => $queryContainer])
            ->willReturn($recreatedDataProvider);
        $result = $this->factory->create($dataProvider, $queryContainer);
        $this->assertNotSame($dataProvider, $result);
        $this->assertSame($recreatedDataProvider, $result);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage must be configured with a search query, but the query is empty
     */
    public function testCreateContainerAwareDataProviderWithoutQuery()
    {
        $this->objectManager->expects($this->never())->method('create');
        /** @var DataProviderInterface $dataProvider */
        $dataProvider = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->factory->create($dataProvider, null);
    }
}
