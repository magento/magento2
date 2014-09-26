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

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\Query\Bool;
use Magento\TestFramework\Helper\ObjectManager;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\ScoreBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scoreBuilder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match
     */
    private $match;

    protected function setUp()
    {
        $helper = new ObjectManager($this);

        $this->scoreBuilder = $this->getMockBuilder('Magento\Framework\Search\Adapter\Mysql\ScoreBuilder')
            ->setMethods(['addCondition'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->match = $helper->getObject('Magento\Framework\Search\Adapter\Mysql\Query\Builder\Match');
    }

    public function testBuildQuery()
    {
        $boost = 3.14;

        /** @var Select|\PHPUnit_Framework_MockObject_MockObject $select */
        $select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->setMethods(['getMatchQuery', 'match'])
            ->disableOriginalConstructor()
            ->getMock();
        $select->expects($this->once())->method('getMatchQuery')
            ->with($this->equalTo(['some_field']), $this->equalTo('-some_value'))
            ->will($this->returnValue('matchedQuery'));
        $select->expects($this->once())->method('match')
            ->with(
                $this->equalTo(['some_field']),
                $this->equalTo('-some_value'),
                $this->equalTo(true),
                $this->equalTo(Select::FULLTEXT_MODE_BOOLEAN)
            );

        /** @var \Magento\Framework\Search\Request\Query\Match|\PHPUnit_Framework_MockObject_MockObject $query */
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->setMethods(['getMatches', 'getValue', 'getBoost'])
            ->disableOriginalConstructor()
            ->getMock();
        $query->expects($this->once())->method('getValue')->willReturn('some_value');
        $query->expects($this->once())->method('getBoost')->willReturn($boost);
        $query->expects($this->once())->method('getMatches')->willReturn([['field' => 'some_field']]);

        $this->scoreBuilder->expects($this->once())->method('addCondition')
            ->with(
                $this->equalTo('matchedQuery'),
                $this->equalTo($boost)
            );

        $result = $this->match->build($this->scoreBuilder, $select, $query, Bool::QUERY_CONDITION_NOT);

        $this->assertEquals($select, $result);
    }
}
