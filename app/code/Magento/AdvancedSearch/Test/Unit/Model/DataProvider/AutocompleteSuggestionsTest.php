<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\DataProvider;

use Magento\AdvancedSearch\Model\DataProvider\AutocompleteSuggestions;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\AdvancedSearch\Model\SuggestedQueries;
use Magento\CatalogSearch\Model\Autocomplete\DataProvider;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Search\Model\Query;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AutocompleteSuggestionsTest extends TestCase
{
    /**
     * @var AutocompleteSuggestions
     */
    private $model;

    /**
     * @var QueryFactory|MockObject
     */
    private $queryFactory;

    /**
     * @var ItemFactory|MockObject
     */
    private $itemFactory;

    /**
     * @var SuggestedQueries|MockObject
     */
    private $suggestedQueries;

    /**
     * @var DataProvider|MockObject
     */
    private $dataProvider;

    /**
     * @var ScopeConfig|MockObject
     */
    private $scopeConfig;

    /**
     * @var Query|MockObject
     */
    private $query;

    protected function setUp(): void
    {
        $this->queryFactory = $this->getMockBuilder(QueryFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemFactory = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->suggestedQueries = $this->getMockBuilder(SuggestedQueries::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dataProvider = $this->getMockBuilder(DataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(ScopeConfig::class)
            ->getMockForAbstractClass();
        $this->query = $this->getMockBuilder(Query::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->queryFactory->expects($this->any())
            ->method('get')
            ->willReturn($this->query);

        $this->model = new AutocompleteSuggestions(
            $this->queryFactory,
            $this->itemFactory,
            $this->scopeConfig,
            $this->suggestedQueries,
            $this->dataProvider
        );
    }

    public function testGetItemsWithEnabledSuggestions(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(true);
        $this->suggestedQueries->expects($this->once())
            ->method('getItems')
            ->with($this->query)
            ->willReturn([]);
        $this->dataProvider->expects($this->never())
            ->method('getItems');
        $this->assertEquals([], $this->model->getItems());
    }

    public function testGetItemsWithDisabledSuggestions(): void
    {
        $this->scopeConfig->expects($this->once())
            ->method('isSetFlag')
            ->with(SuggestedQueriesInterface::SEARCH_SUGGESTION_ENABLED, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);
        $this->suggestedQueries->expects($this->never())
            ->method('getItems');
        $this->dataProvider->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->assertEquals([], $this->model->getItems());
    }
}
