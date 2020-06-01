<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range as RangeFilterBuilder;
use Magento\Framework\Search\Request\Filter\Range as RangeFilterRequest;
use Magento\Framework\Search\Request\Filter\Wildcard;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Range
 */
class RangeTest extends TestCase
{
    /**
     * @var Range
     */
    private $model;

    /**
     * @var FieldMapperInterface|MockObject
     */
    protected $fieldMapper;

    /**
     * @var Wildcard|MockObject
     */
    protected $filterInterface;

    /**
     * Set up test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->fieldMapper = $this->getMockBuilder(FieldMapperInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filterInterface = $this->getMockBuilder(RangeFilterRequest::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getField',
                    'getFrom',
                    'getTo',
                ]
            )
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            RangeFilterBuilder::class,
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
