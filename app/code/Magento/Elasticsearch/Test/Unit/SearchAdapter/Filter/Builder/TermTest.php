<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\SearchAdapter\Filter\Builder;

use Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @see \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term
 */
class TermTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Term
     */
    private $model;

    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapperInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fieldMapper;

    /**
     * @var \Magento\Framework\Search\Request\Filter\Term|\PHPUnit\Framework\MockObject\MockObject
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

        $this->filterInterface = $this->getMockBuilder(\Magento\Framework\Search\Request\Filter\Term::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getValue',
                'getField',
            ])
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\Elasticsearch\SearchAdapter\Filter\Builder\Term::class,
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
