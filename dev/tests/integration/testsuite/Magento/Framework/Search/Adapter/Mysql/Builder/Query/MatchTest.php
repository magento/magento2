<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Builder\Query;

use Magento\Framework\App\ResourceConnection\Config;
use Magento\Framework\Search\Request\Query\BoolExpression;
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\TestFramework\Helper\Bootstrap;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @param string $conditionType
     * @param string $expectedSuffix
     * @dataProvider buildQueryProvider
     */
    public function testBuildQuery($conditionType, $expectedSuffix)
    {
        $conditionPattern = "(LEAST((MATCH (data_index) AGAINST ('%ssomeValue*' IN BOOLEAN MODE)), 1000000)"
            . " * POW(2, %s)) AS score";
        $expectedScoreCondition = sprintf($conditionPattern, $expectedSuffix, ScoreBuilder::WEIGHT_FIELD);
        $expectedSql = "SELECT `someTable`.* FROM `someTable` WHERE (MATCH (data_index) " .
            "AGAINST ('{$expectedSuffix}someValue*' IN BOOLEAN MODE))";

        /** @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder $scoreBuilder */
        $scoreBuilder = $this->objectManager->create('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder');
        /** @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match $match */
        $match = $this->objectManager->create('Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match');
        /** @var \Magento\Framework\Search\Request\Query\Match $query */
        $query = $this->objectManager->create(
            'Magento\Framework\Search\Request\Query\Match',
            [
                'name' => 'Match query',
                'boost' => 3.14,
                'value' => 'someValue',
                'matches' => [
                    ['field' => 'with_boost', 'boost' => 2.15],
                    ['field' => 'without_boost'],
                ]
            ]
        );
        /** @var \Magento\Framework\App\ResourceConnection $resource */
        $resource = $this->objectManager->create('Magento\Framework\App\ResourceConnection');
        /** @var \Magento\Framework\DB\Select $select */
        $select = $resource->getConnection()->select();
        $select->from('someTable');

        $resultSelect = $match->build($scoreBuilder, $select, $query, $conditionType);
        $this->assertEquals($expectedScoreCondition, $scoreBuilder->build());
        $this->assertEquals($expectedSql, $resultSelect->assemble());
    }

    /**
     * @return array
     */
    public function buildQueryProvider()
    {
        return [
            [BoolExpression::QUERY_CONDITION_MUST, '+'],
            [BoolExpression::QUERY_CONDITION_SHOULD, ''],
            [BoolExpression::QUERY_CONDITION_NOT, '-']
        ];
    }
}
