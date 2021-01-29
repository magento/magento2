<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range
 */
class RangeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Range
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Wildcard|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $filterInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fieldMapper = $this->getMockBuilder(\Magento\Elasticsearch\Model\Adapter\FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Range::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getField',
                'getFrom',
                'getTo',
            ])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range::class,
            [
                'fieldMapper' => $this->fieldMapper
            ]
        );
    }

    /**
     *  Test buildFilter method
     */
    public function testBuildFilter()
    {
        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getField')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getFrom')
            ->willReturn('field');

        $this->filterInterface->expects($this->any())
            ->method('getTo')
            ->willReturn('field');

        $result = $this->model->buildFilter($this->filterInterface);
        $this->assertNotNull($result);
    }
}
