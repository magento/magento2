<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Search\Model;

use Magento\TestFramework\Helper\ObjectManager;

class QueryResultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * @dataProvider getPropertiesDataProvider
     */
    public function testGetProperties($queryText, $resultsCount)
    {
        /** @var \Magento\Search\Model\QueryResult $queryResult */
        $queryResult = $this->objectManager->getObject(
            'Magento\Search\Model\QueryResult',
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
    public function getPropertiesDataProvider()
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
