<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Search\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Search\Model\QueryResult;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class QueryResultTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    #[DataProvider('getPropertiesDataProvider')]
    public function testGetProperties($queryText, $resultsCount)
    {
        /** @var QueryResult $queryResult */
        $queryResult = $this->objectManager->getObject(
            QueryResult::class,
            [
                'queryText' => $queryText,
                'resultsCount' => $resultsCount,
            ]
        );
        $this->assertEquals($queryText, $queryResult->getQueryText());
        $this->assertEquals($resultsCount, $queryResult->getResultsCount());
    }

    /**
     * Data provider for testGetProperties
     * @return array
     */
    public static function getPropertiesDataProvider()
    {
        return [
            [
                'queryText' => 'Some kind of query text',
                'resultsCount' => 0,
            ],
            [
                'queryText' => 'Another query',
                'resultsCount' => 322312312,
            ],
            [
                'queryText' => 'It\' a query too',
                'resultsCount' => -100,
            ],
            [
                'queryText' => '',
                'resultsCount' => null,
            ],
            [
                'queryText' => 42,
                'resultsCount' => false,
            ],
        ];
    }
}
