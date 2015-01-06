<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Data;

use Magento\Framework\Api\CriteriaInterface;

/**
 * Class AbstractCriteriaTest
 */
class AbstractCriteriaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Criteria\Sample
     */
    protected $criteria;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->criteria = $objectManager->getObject('Magento\Framework\Data\Criteria\Sample');
    }

    /**
     * Run test addField method
     *
     * @param string|array $field
     * @param string|null $alias
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderAddField
     */
    public function testAddField($field, $alias, array $result)
    {
        $this->criteria->addField($field, $alias);
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_FIELDS]['list']);
    }

    /**
     * Run test addFilter method
     *
     * @param string $name
     * @param string|array $field
     * @param string|int|array $condition
     * @param string $type
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderAddFilter
     */
    public function testAddFilter($name, $field, $condition, $type, array $result)
    {
        $this->criteria->addFilter($name, $field, $condition, $type);
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_FILTERS]['list']);
    }

    /**
     * Run test addOrder method
     *
     * @param string $field
     * @param string $direction
     * @param bool $unShift
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderAddOrder
     */
    public function testAddOrder($field, $direction, $unShift, array $result)
    {
        $this->criteria->addOrder($field, $direction, $unShift);
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_ORDERS]['list']);
    }

    /**
     * Run test setLimit method
     *
     * @param int $offset
     * @param int $size
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderSetLimit
     */
    public function testSetLimit($offset, $size, array $result)
    {
        $this->criteria->setLimit($offset, $size);
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_LIMIT]);
    }

    /**
     * Run test removeField method
     *
     * @param array $actualField
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderRemoveField
     */
    public function testRemoveField(array $actualField, $field, $isAlias, array $result)
    {
        list($name, $alias) = $actualField;
        $this->criteria->addField($name, $alias);

        $this->criteria->removeField($field, $isAlias);
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_FIELDS]['list']);
    }

    /**
     * Run test removeAllFields method
     *
     * @param array $actualField
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderRemoveAllFields
     */
    public function testRemoveAllFields(array $actualField, array $result)
    {
        list($name, $alias) = $actualField;
        $this->criteria->addField($name, $alias);

        $this->criteria->removeAllFields();
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_FIELDS]['list']);
    }

    /**
     * Run test removeFilter method
     *
     * @param array $actualField
     * @param string $name
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderRemoveFilter
     */
    public function testRemoveFilter(array $actualField, $name, array $result)
    {
        list($filterName, $field, $condition, $type) = $actualField;
        $this->criteria->addFilter($filterName, $field, $condition, $type);

        $this->criteria->removeFilter($name);
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_FILTERS]['list']);
    }

    /**
     * Run test removeAllFilters method
     *
     * @param array $actualField
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderRemoveAllFilters
     */
    public function testRemoveAllFilters(array $actualField, array $result)
    {
        list($filterName, $field, $condition, $type) = $actualField;
        $this->criteria->addFilter($filterName, $field, $condition, $type);

        $this->criteria->removeAllFilters();
        $this->assertEquals($result, $this->criteria->toArray()[CriteriaInterface::PART_FILTERS]['list']);
    }

    /**
     * Run test reset method
     *
     * @param array $result
     * @return void
     *
     * @dataProvider dataProviderReset
     */
    public function testReset(array $result)
    {
        $this->criteria->reset();
        $this->assertEquals($result, $this->criteria->toArray());
    }

    /**
     * Data provider for reset method
     *
     * @return array
     */
    public function dataProviderReset()
    {
        return [
            [
                'result' => [
                    'fields' => [
                        'list' => [],
                    ],
                    'filters' => [
                        'list' => [],
                    ],
                    'orders' => [
                        'list' => [],
                    ],
                    'criteria_list' => [
                        'list' => [],
                    ],
                ],
            ]
        ];
    }

    /**
     * Data provider for removeAllFilters method
     *
     * @return array
     */
    public function dataProviderRemoveAllFilters()
    {
        return [
            [
                'actualResult' => [
                    'test-filter-name',
                    'test-field-name',
                    'test-condition',
                    'test-type',
                ],
                'result' => [],
            ]
        ];
    }

    /**
     * Data provider for removeFilter method
     *
     * @return array
     */
    public function dataProviderRemoveFilter()
    {
        return [
            [
                'actualResult' => [
                    'test-filter-name',
                    'test-field-name',
                    'test-condition',
                    'test-type',
                ],
                'name' => 'test-filter-name',
                'result' => [],
            ]
        ];
    }

    /**
     * Data provider for removeAllFields method
     *
     * @return array
     */
    public function dataProviderRemoveAllFields()
    {
        return [
            [
                'actualField' => [
                    'test-field-name',
                    'test-field-alias',
                ],
                'result' => [],
            ]
        ];
    }

    /**
     * Data provider for removeField method
     *
     * @return array
     */
    public function dataProviderRemoveField()
    {
        return [
            [
                'actualField' => [
                    'test-field-name',
                    null,
                ],
                'field' => 'test-field-name',
                'isAlias' => false,
                'result' => [],
            ],
            [
                'actualField' => [
                    '*',
                    null,
                ],
                'field' => '*',
                'isAlias' => false,
                'result' => []
            ],
            [
                'actualField' => [
                    'test-field-name',
                    'test-field-alias',
                ],
                'field' => 'test-field-alias',
                'isAlias' => true,
                'result' => []
            ]
        ];
    }

    /**
     * Data provider for setLimit method
     *
     * @return array
     */
    public function dataProviderSetLimit()
    {
        return [
            [
                'offset' => 99,
                'size' => 30,
                'result' => [99, 30],
            ]
        ];
    }

    /**
     * Data provider for addOrder method
     *
     * @return array
     */
    public function dataProviderAddOrder()
    {
        return [
            [
                'field' => 'test-field-name',
                'direction' => 'desc',
                'unShift' => false,
                'result' => [

                    'test-field-name' => 'DESC',
                ],
            ],
            [
                'field' => 'test-field-name',
                'direction' => 'asc',
                'unShift' => false,
                'result' => [
                    'test-field-name' => 'ASC',
                ]
            ],
            [
                'field' => 'test-field-name',
                'direction' => 'fail',
                'unShift' => false,
                'result' => [
                    'test-field-name' => 'DESC',
                ]
            ]
        ];
    }

    /**
     * Data provider for addFilter
     *
     * @return array
     */
    public function dataProviderAddFilter()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        return [
            [
                'name' => 'test-filter-name',
                'field' => 'test-field-name',
                'condition' => 'test-condition',
                'type' => 'test-type',
                'result' => [
                    'test-filter-name' => $objectManager->getObject(
                        'Magento\Framework\Object',
                        [
                            'data' => [
                                'name' => 'test-filter-name',
                                'field' => 'test-field-name',
                                'condition' => 'test-condition',
                                'type' => 'test-type',
                            ]
                        ]
                    ),
                ],
            ]
        ];
    }

    /**
     * Data provider for addField
     *
     * @return array
     */
    public function dataProviderAddField()
    {
        return [
            [
                'field' => 'test-field-name',
                'alias' => null,
                'result' => [
                    'test-field-name' => 'test-field-name',
                ],
            ],
            [
                'field' => '*',
                'alias' => null,
                'result' => [
                    '*',
                ],
            ],
            [
                'field' => [
                    'test-field-name-1',
                    'test-field-name-2',
                    'test-field-name-3',
                ],
                'alias' => null,
                'result' => [
                    'test-field-name-1' => 'test-field-name-1',
                    'test-field-name-2' => 'test-field-name-2',
                    'test-field-name-3' => 'test-field-name-3',
                ]
            ],
            [
                'field' => 'test-field-name',
                'alias' => 'alias-test',
                'result' => [
                    'alias-test' => 'test-field-name',
                ]
            ],
            [
                'field' => '*',
                'alias' => null,
                'result' => [
                    '*',
                ]
            ],
            [
                'field' => [
                    'alias-1' => 'test-field-name',
                    'alias-2' => 'test-field-name',
                    'alias-3' => 'test-field-name',
                ],
                'alias' => null,
                'result' => [
                    'alias-1' => 'test-field-name',
                    'alias-2' => 'test-field-name',
                    'alias-3' => 'test-field-name',
                ]
            ]
        ];
    }
}
