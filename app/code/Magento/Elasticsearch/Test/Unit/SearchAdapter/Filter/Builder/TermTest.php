<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;
use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term as TermBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term
 */
class TermTest extends TestCase
{
    /**
     * @var Term
     */
    private $model;

    /**
     * @var FieldMapperInterface|MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Term|MockObject
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

        $this->filterInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getValue',
                'getField',
            ])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            TermBuilder::class,
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
        $this->filterInterface->expects($this->any())
            ->method('getValue')
            ->willReturn('value');

        $this->filterInterface->expects($this->any())
            ->method('getField')
            ->willReturn('field');

        $this->fieldMapper->expects($this->any())
            ->method('getFieldName')
            ->willReturn('field');

        $result = $this->model->buildFilter($this->filterInterface);
        $this->assertNotNull($result);
    }
}
