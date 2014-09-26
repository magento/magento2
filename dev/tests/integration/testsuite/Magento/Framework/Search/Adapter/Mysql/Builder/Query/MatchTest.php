<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql\Builder\Query;

use Magento\Framework\App\Resource\Config;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\TestFramework\Helper\Bootstrap;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager
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
        $expectedScoreCondition = "(MATCH (with_boost, without_boost) AGAINST ('{$expectedSuffix}someValue' " .
            "IN BOOLEAN MODE) * 3.14) AS global_score";
        $expectedSql = "SELECT `someTable`.* FROM `someTable` WHERE (MATCH (with_boost, without_boost) " .
            "AGAINST ('{$expectedSuffix}someValue' IN BOOLEAN MODE))";

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
                    ['field' => 'without_boost']
                ]
            ]
        );
        /** @var \Magento\Framework\App\Resource $resource */
        $resource = $this->objectManager->create('Magento\Framework\App\Resource');
        /** @var \Magento\Framework\DB\Select $select */
        $select = $resource->getConnection(Config::DEFAULT_SETUP_CONNECTION)->select();
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
            [Bool::QUERY_CONDITION_MUST, '+'],
            [Bool::QUERY_CONDITION_SHOULD, ''],
            [Bool::QUERY_CONDITION_NOT, '-']
        ];
    }
}
