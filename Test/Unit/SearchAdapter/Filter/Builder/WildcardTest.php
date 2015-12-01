<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard;

/**
 * @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Wildcard
 */
class WildcardTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Wildcard
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\FieldMapperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Wildcard|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->fieldMapper = $this->getMockBuilder('Magento\Elasticsearch\SearchAdapter\FieldMapperInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterInterface = $this->getMockBuilder('Magento\Framework\Search\Request\Filter\Wildcard')
            ->disableOriginalConstructor()
            ->setMethods([
                'getField',
                'getValue',
            ])
            ->getMock();

        $this->model = new Wildcard(
            $this->fieldMapper
        );
    }

    public function testBuildFilter()
    {
        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getField')
            ->willReturn('field');

        $this->model->buildFilter($this->filterInterface);
    }
}
