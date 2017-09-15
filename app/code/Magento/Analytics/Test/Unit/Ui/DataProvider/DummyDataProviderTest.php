<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Test\Unit\Ui\DataProvider;

use Magento\Analytics\Ui\DataProvider\DummyDataProvider;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DummyDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchResultMock;

    /**
     * @var SearchCriteriaInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dataCollectionMock;

    /**
     * @var Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterMock;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var DummyDataProvider
     */
    private $dummyDataProvider;

    /**
     * @var string
     */
    private $providerName = 'data_provider_name';

    /**
     * @var array
     */
    private $configData = ['field' => 'value'];

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->searchResultMock = $this->getMockBuilder(SearchResultInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterMock = $this->getMockBuilder(Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->dummyDataProvider = $this->objectManagerHelper->getObject(
            DummyDataProvider::class,
            [
                'name' => $this->providerName,
                'searchResult' => $this->searchResultMock,
                'searchCriteria' => $this->searchCriteriaMock,
                'collection' => $this->dataCollectionMock,
                'data' => ['config' => $this->configData],
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetName()
    {
        $this->assertSame($this->providerName, $this->dummyDataProvider->getName());
    }

    /**
     * @return void
     */
    public function testGetConfigData()
    {
        $this->assertSame($this->configData, $this->dummyDataProvider->getConfigData());
        $dataProvider = $this->objectManagerHelper
            ->getObject(
                DummyDataProvider::class,
                []
            );
        $this->assertSame([], $dataProvider->getConfigData());
    }

    /**
     * @return void
     */
    public function testSetConfigData()
    {
        $configValue = ['key' => 'value'];

        $this->assertTrue($this->dummyDataProvider->setConfigData($configValue));
        $this->assertSame($configValue, $this->dummyDataProvider->getConfigData());
    }

    /**
     * @return void
     */
    public function testGetMeta()
    {
        $this->assertSame([], $this->dummyDataProvider->getMeta());
    }

    /**
     * @return void
     */
    public function testGetFieldMetaInfo()
    {
        $this->assertSame([], $this->dummyDataProvider->getFieldMetaInfo('', ''));
    }

    /**
     * @return void
     */
    public function testGetFieldSetMetaInfo()
    {
        $this->assertSame([], $this->dummyDataProvider->getFieldSetMetaInfo(''));
    }

    /**
     * @return void
     */
    public function testGetFieldsMetaInfo()
    {
        $this->assertSame([], $this->dummyDataProvider->getFieldsMetaInfo(''));
    }

    /**
     * @return void
     */
    public function testGetPrimaryFieldName()
    {
        $this->assertSame('', $this->dummyDataProvider->getPrimaryFieldName());
    }

    /**
     * @return void
     */
    public function testGetRequestFieldName()
    {
        $this->assertSame('', $this->dummyDataProvider->getRequestFieldName());
    }

    /**
     * @return void
     */
    public function testGetData()
    {
        $this->dataCollectionMock
            ->expects($this->once())
            ->method('toArray')
            ->willReturn([]);
        $this->assertSame([], $this->dummyDataProvider->getData());
    }

    /**
     * @return void
     */
    public function testAddFilter()
    {
        $this->assertNull($this->dummyDataProvider->addFilter($this->filterMock));
    }

    /**
     * @return void
     */
    public function testAddOrder()
    {
        $this->assertNull($this->dummyDataProvider->addOrder('', ''));
    }

    /**
     * @return void
     */
    public function testSetLimit()
    {
        $this->assertNull($this->dummyDataProvider->setLimit(1, 1));
    }

    /**
     * @return void
     */
    public function testGetSearchCriteria()
    {
        $this->assertSame($this->searchCriteriaMock, $this->dummyDataProvider->getSearchCriteria());
    }

    /**
     * @return void
     */
    public function testGetSearchResult()
    {
        $this->assertSame($this->searchResultMock, $this->dummyDataProvider->getSearchResult());
    }
}
