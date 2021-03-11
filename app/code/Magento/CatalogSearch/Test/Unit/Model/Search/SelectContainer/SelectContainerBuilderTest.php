<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\SelectContainer;

use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerBuilder;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainerFactory;
use Magento\Framework\Search\RequestInterface;
use Magento\CatalogSearch\Model\Search\QueryChecker\FullTextSearchCheck;
use Magento\CatalogSearch\Model\Search\CustomAttributeFilterCheck;
use Magento\CatalogSearch\Model\Search\FiltersExtractor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Search\Request\Filter\Term;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SelectContainerBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SelectContainerFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $selectContainerFactoryMock;

    /**
     * @var FullTextSearchCheck|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fullTextSearchCheckMock;

    /**
     * @var CustomAttributeFilterCheck|\PHPUnit\Framework\MockObject\MockObject
     */
    private $customAttributeFilterCheckMock;

    /**
     * @var FiltersExtractor|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filtersExtractorMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $scopeConfigInterfaceMock;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var RequestInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $requestMock;

    /**
     * @var SelectContainerBuilder
     */
    private $selectContainerBuilder;

    protected function setUp(): void
    {
        $this->selectContainerFactoryMock = $this->getMockBuilder(SelectContainerFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->fullTextSearchCheckMock = $this->getMockBuilder(FullTextSearchCheck::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->customAttributeFilterCheckMock = $this->getMockBuilder(CustomAttributeFilterCheck::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filtersExtractorMock = $this->getMockBuilder(FiltersExtractor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfigInterfaceMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->selectContainerBuilder = $objectManagerHelper->getObject(
            SelectContainerBuilder::class,
            [
                'selectContainerFactory' => $this->selectContainerFactoryMock,
                'fullTextSearchCheck' => $this->fullTextSearchCheckMock,
                'customAttributeFilterCheck' => $this->customAttributeFilterCheckMock,
                'filtersExtractor' => $this->filtersExtractorMock,
                'scopeConfig' => $this->scopeConfigInterfaceMock,
                'resource' => $this->resourceConnectionMock
            ]
        );
    }

    public function testBuildByRequest()
    {
        list($visibilityFilter, $customFilter, $nonCustomFilter) = $this->mockFilters();
        $requestGetQueryResult = $this->mockQuery();
        $requestGetIndexResult = 'space-banana';
        $requestGetDimensionsResult = [1, 2, 3];
        $fullTextSearchCheckResult = true;
        $isShowOutOfStockEnabled = true;
        $selectContainerMock = $this->getMockBuilder(SelectContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock
            ->method('getQuery')
            ->willReturn($requestGetQueryResult);

        $this->requestMock
            ->method('getIndex')
            ->willReturn($requestGetIndexResult);

        $this->requestMock
            ->method('getDimensions')
            ->willReturn($requestGetDimensionsResult);

        $this->filtersExtractorMock
            ->method('extractFiltersFromQuery')
            ->with($requestGetQueryResult)
            ->willReturn([$visibilityFilter, $customFilter, $nonCustomFilter]);

        $this->customAttributeFilterCheckMock
            ->method('isCustom')
            ->withConsecutive([$visibilityFilter], [$customFilter], [$nonCustomFilter])
            ->will($this->onConsecutiveCalls(true, true, false));

        $this->fullTextSearchCheckMock
            ->method('isRequiredForQuery')
            ->with($requestGetQueryResult)
            ->willReturn($fullTextSearchCheckResult);

        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $connectionMock
            ->method('select')
            ->willReturn($selectMock);

        $this->resourceConnectionMock
            ->method('getConnection')
            ->willReturn($connectionMock);

        $this->scopeConfigInterfaceMock
            ->method('isSetFlag')
            ->with('cataloginventory/options/show_out_of_stock', ScopeInterface::SCOPE_STORE)
            ->willReturn($isShowOutOfStockEnabled);

        $this->selectContainerFactoryMock
            ->method('create')
            ->with([
                'nonCustomAttributesFilters' => [$nonCustomFilter],
                'customAttributesFilters' => [$customFilter],
                'visibilityFilter' => $visibilityFilter,
                'isFullTextSearchRequired' => $fullTextSearchCheckResult,
                'isShowOutOfStockEnabled' => $isShowOutOfStockEnabled,
                'usedIndex' => $requestGetIndexResult,
                'dimensions' => $requestGetDimensionsResult,
                'select' => $selectMock
            ])->willReturn($selectContainerMock);

        $this->assertSame(
            $selectContainerMock,
            $this->selectContainerBuilder->buildByRequest($this->requestMock)
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function mockQuery()
    {
        return $this->getMockBuilder(QueryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @return array
     */
    private function mockFilters()
    {
        $visibilityFilter = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name1', 'value1', 'visibility'])
            ->setMethods(null)
            ->getMock();

        $customFilter = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name2', 'value2', 'field1'])
            ->setMethods(null)
            ->getMock();

        $nonCustomFilter = $this->getMockBuilder(Term::class)
            ->setConstructorArgs(['name3', 'value3', 'field2'])
            ->setMethods(null)
            ->getMock();

        return [$visibilityFilter, $customFilter, $nonCustomFilter];
    }
}
