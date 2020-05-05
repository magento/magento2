<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Search\Test\Unit\Adapter\Mysql\Filter\Builder;

use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Request\Filter\Term;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TermTest extends TestCase
{
    /**
     * @var Term|MockObject
     */
    private $requestFilter;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term
     */
    private $filter;

    /**
     * @var ConditionManager|MockObject
     */
    private $conditionManager;

    /**
     * Set up
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->requestFilter = $this->getMockBuilder(Term::class)
            ->setMethods(['getField', 'getValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->conditionManager = $this->getMockBuilder(ConditionManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateCondition'])
            ->getMock();
        $this->conditionManager->expects($this->any())
            ->method('generateCondition')
            ->willReturnCallback(
                function ($field, $operator, $value) {
                    return sprintf(
                        is_array($value) ? '%s %s (%s)' : '%s %s %s',
                        $field,
                        $operator,
                        is_array($value) ? implode(', ', $value) : $value
                    );
                }
            );

        $this->filter = $objectManager->getObject(
            \Magento\Framework\Search\Adapter\Mysql\Filter\Builder\Term::class,
            [
                'conditionManager' => $this->conditionManager,
            ]
        );
    }

    /**
     * @param string $field
     * @param string $value
     * @param bool $isNegation
     * @param string $expectedResult
     * @dataProvider buildQueryDataProvider
     */
    public function testBuildQuery($field, $value, $isNegation, $expectedResult)
    {
        $this->requestFilter->expects($this->once())
            ->method('getField')
            ->willReturn($field);
        $this->requestFilter->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturn($value);

        $actualResult = $this->filter->buildFilter($this->requestFilter, $isNegation);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Data provider for BuildQuery
     *
     * @return array
     */
    public function buildQueryDataProvider()
    {
        return [
            'positive' => [
                'field' => 'testField',
                'value' => 'testValue',
                'isNegation' => false,
                'expectedResult' => 'testField = testValue',
            ],
            'negative' => [
                'field' => 'testField2',
                'value' => 'testValue2',
                'isNegation' => true,
                'expectedResult' => 'testField2 != testValue2',
            ],
            'positiveIn' => [
                'field' => 'testField2',
                'value' => ['testValue2'],
                'isNegation' => false,
                'expectedResult' => 'testField2 IN (testValue2)',
            ],
            'negativeIn' => [
                'field' => 'testField2',
                'value' => ['testValue2'],
                'isNegation' => true,
                'expectedResult' => 'testField2 NOT IN (testValue2)',
            ]
        ];
    }
}
