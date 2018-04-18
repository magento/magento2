<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\Rule\Condition;

use Magento\Eav\Model\Config as EavConfig;
use Magento\CatalogRule\Model\Rule\Condition\MappableConditionsProcessor;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionProviderInterface;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CombinedCondition;
use Magento\CatalogRule\Model\Rule\Condition\Product as SimpleCondition;

class MappableConditionProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MappableConditionsProcessor
     */
    private $mappableConditionProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customConditionProcessorBuilderMock;

    protected function setUp()
    {
        $this->eavConfigMock = $this->getMockBuilder(EavConfig::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttribute'])
            ->getMock();

        $this->customConditionProcessorBuilderMock = $this->getMockBuilder(
            CustomConditionProviderInterface::class
        )->disableOriginalConstructor()
        ->setMethods(['hasProcessorForField'])
        ->getMockForAbstractClass();

        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->mappableConditionProcessor = $this->objectManagerHelper->getObject(
            MappableConditionsProcessor::class,
            [
                'customConditionProvider' => $this->customConditionProcessorBuilderMock,
                'eavConfig' => $this->eavConfigMock,
            ]
        );
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  condition-1 => [ attribute => field-1 ]
     *                  condition-2 => [ attribute => field-2 ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-2 is not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions => []
     *      ]
     * ]
     */
    public function testConditionV1()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $inputCondition = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'any'
        );

        $validResult = $this->getMockForCombinedCondition([], 'any');

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, true],
                        [$field2, false],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions =>
     *              [
     *                  condition-1 => [ attribute => field-1 ]
     *                  condition-2 => [ attribute => field-2 ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-2 is not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions =>
     *              [
     *                  condition-1 => [ attribute => field-1 ]
     *              ]
     *      ]
     * ]
     */
    public function testConditionV2()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $inputCondition = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'all'
        );

        $validResult = $this->getMockForCombinedCondition(
            [
                $simpleCondition1
            ],
            'all'
        );

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, true],
                        [$field2, false],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions =>
     *              [
     *                  condition-1 => [ attribute => field-1 ]
     *                  condition-2 => [ attribute => field-2 ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-1 and condition-2 are not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions => []
     *      ]
     * ]
     */
    public function testConditionV3()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $inputCondition = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'all'
        );

        $validResult = $this->getMockForCombinedCondition([], 'all');

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, false],
                        [$field2, false],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => all
     *                          conditions =>
     *                              [
     *                                  condition-1 => [ attribute => field-1 ]
     *                                  condition-2 => [ attribute => field-2 ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-1 is not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => all
     *                          conditions =>
     *                              [
     *                                  condition-2 => [ attribute => field-2 ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     */
    public function testConditionV4()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $subCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'all'
        );

        $field3 = 'field-3';
        $field4 = 'field-4';

        $simpleCondition3 = $this->getMockForSimpleCondition($field3);
        $simpleCondition4 = $this->getMockForSimpleCondition($field4);
        $subCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );

        $inputCondition = $this->getMockForCombinedCondition(
            [
                $subCondition1,
                $subCondition2
            ],
            'any'
        );

        $validSubCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition2
            ],
            'all'
        );
        $validSubCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );
        $validResult = $this->getMockForCombinedCondition(
            [
                $validSubCondition1,
                $validSubCondition2
            ],
            'any'
        );

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, false],
                        [$field2, true],
                        [$field3, true],
                        [$field4, true],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-1 => [ attribute => field-1 ]
     *                                  condition-2 => [ attribute => field-2 ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-1 is not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     */
    public function testConditionV5()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $subCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'any'
        );

        $field3 = 'field-3';
        $field4 = 'field-4';

        $simpleCondition3 = $this->getMockForSimpleCondition($field3);
        $simpleCondition4 = $this->getMockForSimpleCondition($field4);
        $subCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );

        $inputCondition = $this->getMockForCombinedCondition(
            [
                $subCondition1,
                $subCondition2
            ],
            'all'
        );

        $validSubCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );
        $validResult = $this->getMockForCombinedCondition(
            [
                $validSubCondition2
            ],
            'all'
        );

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, false],
                        [$field2, true],
                        [$field3, true],
                        [$field4, true],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => all
     *          conditions =>
     *              [
     *                  condition-1 => [ attribute => field-1 ]
     *                  condition-2 => [ attribute => field-2 ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     *
     * in case when all condition are mappable there must not be any changes to input
     */
    public function testConditionV6()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);

        $field3 = 'field-3';
        $field4 = 'field-4';

        $simpleCondition3 = $this->getMockForSimpleCondition($field3);
        $simpleCondition4 = $this->getMockForSimpleCondition($field4);
        $subCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );

        $inputCondition = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2,
                $subCondition1
            ],
            'all'
        );

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, true],
                        [$field2, true],
                        [$field3, true],
                        [$field4, true],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($inputCondition, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => all
     *                          conditions =>
     *                              [
     *                                  condition-1 => [ attribute => field-1 ]
     *                                  combined-condition =>
     *                                      [
     *                                          aggregation => any
     *                                          conditions =>
     *                                              [
     *                                                  condition-2 => [ attribute => field-2 ]
     *                                                  condition-3 => [ attribute => field-3 ]
     *                                              ]
     *                                      ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => all
     *                          conditions =>
     *                              [
     *                                  combined-condition =>
     *                                      [
     *                                          aggregation => any
     *                                          conditions =>
     *                                              [
     *                                                  condition-4 => [ attribute => field-4 ]
     *                                                  condition-5 => [ attribute => field-5 ]
     *                                              ]
     *                                      ]
     *                                  combined-condition =>
     *                                      [
     *                                          aggregation => any
     *                                          conditions =>
     *                                              [
     *                                                  condition-6 => [ attribute => field-6 ]
     *                                                  condition-7 => [ attribute => field-7 ]
     *                                              ]
     *                                      ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-3 and condition-5 are not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => all
     *                          conditions =>
     *                              [
     *                                  condition-1 => [ attribute => field-1 ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => all
     *                          conditions =>
     *                              [
     *                                  combined-condition =>
     *                                      [
     *                                          aggregation => any
     *                                          conditions =>
     *                                              [
     *                                                  condition-6 => [ attribute => field-6 ]
     *                                                  condition-7 => [ attribute => field-7 ]
     *                                              ]
     *                                      ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testConditionV7()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';
        $field3 = 'field-3';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $simpleCondition3 = $this->getMockForSimpleCondition($field3);

        $subCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition2,
                $simpleCondition3
            ],
            'any'
        );
        $subCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $subCondition1
            ],
            'all'
        );

        $field4 = 'field-4';
        $field5 = 'field-5';
        $field6 = 'field-6';
        $field7 = 'field-7';

        $simpleCondition4 = $this->getMockForSimpleCondition($field4);
        $simpleCondition5 = $this->getMockForSimpleCondition($field5);
        $simpleCondition6 = $this->getMockForSimpleCondition($field6);
        $simpleCondition7 = $this->getMockForSimpleCondition($field7);

        $subCondition3 = $this->getMockForCombinedCondition(
            [
                $simpleCondition4,
                $simpleCondition5
            ],
            'any'
        );
        $subCondition4 = $this->getMockForCombinedCondition(
            [
                $simpleCondition6,
                $simpleCondition7
            ],
            'any'
        );
        $subCondition5 = $this->getMockForCombinedCondition(
            [
                $subCondition3,
                $subCondition4
            ],
            'all'
        );

        $inputCondition = $this->getMockForCombinedCondition(
            [
                $subCondition2,
                $subCondition5
            ],
            'any'
        );

        $validSubCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition1
            ],
            'all'
        );
        $validSubCondition4 = $this->getMockForCombinedCondition(
            [
                $subCondition4
            ],
            'all'
        );

        $validResult = $this->getMockForCombinedCondition(
            [
                $validSubCondition2,
                $validSubCondition4
            ],
            'any'
        );

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, true],
                        [$field2, true],
                        [$field3, false],
                        [$field4, true],
                        [$field5, false],
                        [$field6, true],
                        [$field7, true],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-1 => [ attribute => field-1 ]
     *                                  condition-2 => [ attribute => field-2 ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-1 and condition-4 are not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions => []
     * ]
     */
    public function testConditionV8()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $subCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'any'
        );

        $field3 = 'field-3';
        $field4 = 'field-4';

        $simpleCondition3 = $this->getMockForSimpleCondition($field3);
        $simpleCondition4 = $this->getMockForSimpleCondition($field4);
        $subCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );

        $inputCondition = $this->getMockForCombinedCondition(
            [
                $subCondition1,
                $subCondition2
            ],
            'any'
        );

        $validResult = $this->getMockForCombinedCondition([], 'any');

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, false],
                        [$field2, true],
                        [$field3, true],
                        [$field4, false],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * input condition tree:
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions =>
     *              [
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-1 => [ attribute => field-1 ]
     *                                  condition-2 => [ attribute => field-2 ]
     *                              ]
     *                      ]
     *                  combined-condition =>
     *                      [
     *                          aggregation => any
     *                          conditions =>
     *                              [
     *                                  condition-3 => [ attribute => field-3 ]
     *                                  condition-4 => [ attribute => field-4 ]
     *                              ]
     *                      ]
     *                  condition-5 => [ attribute => field-5 ]
     *              ]
     *      ]
     * ]
     *
     * in case when condition-1 and condition-4 are not mappable the result must be next:
     *
     * [
     *  combined-condition =>
     *      [
     *          aggregation => any
     *          conditions => []
     * ]
     */
    public function testConditionV9()
    {
        $field1 = 'field-1';
        $field2 = 'field-2';

        $simpleCondition1 = $this->getMockForSimpleCondition($field1);
        $simpleCondition2 = $this->getMockForSimpleCondition($field2);
        $subCondition1 = $this->getMockForCombinedCondition(
            [
                $simpleCondition1,
                $simpleCondition2
            ],
            'any'
        );

        $field3 = 'field-3';
        $field4 = 'field-4';

        $simpleCondition3 = $this->getMockForSimpleCondition($field3);
        $simpleCondition4 = $this->getMockForSimpleCondition($field4);
        $subCondition2 = $this->getMockForCombinedCondition(
            [
                $simpleCondition3,
                $simpleCondition4
            ],
            'any'
        );

        $field5 = 'field-5';
        $simpleCondition5 = $this->getMockForSimpleCondition($field5);

        $inputCondition = $this->getMockForCombinedCondition(
            [
                $subCondition1,
                $subCondition2,
                $simpleCondition5
            ],
            'any'
        );

        $validResult = $this->getMockForCombinedCondition([], 'any');

        $this->customConditionProcessorBuilderMock
            ->method('hasProcessorForField')
            ->will(
                $this->returnValueMap(
                    [
                        [$field1, false],
                        [$field2, true],
                        [$field3, true],
                        [$field4, false],
                        [$field5, true],
                    ]
                )
            );

        $this->eavConfigMock
            ->method('getAttribute')
            ->willReturn(null);

        $result = $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);

        $this->assertEquals($validResult, $result);
    }

    /**
     * @expectedException \Magento\Framework\Exception\InputException
     * @expectedExceptionMessage Undefined condition type "olo-lo" passed in.
     */
    public function testException()
    {
        $simpleCondition = $this->getMockForSimpleCondition('field');
        $simpleCondition->setType('olo-lo');
        $inputCondition = $this->getMockForCombinedCondition([$simpleCondition], 'any');

        $this->mappableConditionProcessor->rebuildConditionsTree($inputCondition);
    }

    protected function getMockForCombinedCondition($subConditions, $aggregator)
    {
        $mock = $this->getMockBuilder(CombinedCondition::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $mock->setConditions($subConditions);
        $mock->setAggregator($aggregator);
        $mock->setType(CombinedCondition::class);

        return $mock;
    }

    protected function getMockForSimpleCondition($attribute)
    {
        $mock = $this->getMockBuilder(SimpleCondition::class)
            ->disableOriginalConstructor()
            ->setMethods()
            ->getMock();

        $mock->setAttribute($attribute);
        $mock->setType(SimpleCondition::class);

        return $mock;
    }
}
