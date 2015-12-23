<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Query\Builder;

use Magento\Elasticsearch\SearchAdapter\Query\Builder\Match;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Match
     */
    protected $model;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Framework\Search\Request\QueryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    public function setUp()
    {
        $this->fieldMapper = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\FieldMapperInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryInterface = $this->getMockBuilder('Magento\Framework\Search\Request\QueryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            '\Magento\Elasticsearch\SearchAdapter\Query\Builder\Match',
            [
                'fieldMapper' => $this->fieldMapper
            ]
        );

    }

    /**
     * Test build() method
     */
    public function testBuild()
    {
        $query = $this->getMockBuilder('Magento\Framework\Search\Request\Query\Match')
            ->setMethods(['getValue', 'getMatches'])
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())->method('getValue')->willReturn('query_value');
        $query->expects($this->once())->method('getMatches')->willReturn([['field' => 'some_field'], ]);
        $this->model->build([], $query, 'not');
    }
}
