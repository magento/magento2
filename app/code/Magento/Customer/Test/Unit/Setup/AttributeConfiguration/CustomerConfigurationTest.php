<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Setup\AttributeConfiguration;

use Magento\Customer\Setup\AttributeConfiguration\CustomerConfiguration;
use Magento\Eav\Setup\AttributeConfiguration\MainConfiguration;
use Magento\Eav\Setup\AttributeConfiguration\Provider\ProviderInterface;
use Magento\Eav\Setup\AttributeConfiguration\Provider\ScopeProvider;

class CustomerConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CustomerConfiguration
     */
    private $builder;

    /**
     * @var MainConfiguration
     */
    private $mainConfiguration;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ProviderInterface $inputTypeProviderMock */
        $inputTypeProviderMock = $this->getMockBuilder(ProviderInterface::class)
                                      ->setMethods(['resolve'])
                                      ->getMockForAbstractClass();

        $this->mainConfiguration = new MainConfiguration($inputTypeProviderMock, new ScopeProvider());

        $this->builder = new CustomerConfiguration($this->mainConfiguration);
    }

    public function testBuilderReturnsACompatibleArray()
    {
        $builder = $this->builder;

        foreach ($this->getMethodsThatChangeState() as $methodInfo) {
            $this->builder = call_user_func_array([$this->builder, $methodInfo[0]], $methodInfo[1]);
            $this->assertNotSame($builder, $this->builder);
        }

        $this->assertEquals(
            [
                'is_filterable_in_grid' => true,
                'is_searchable_in_grid' => false,
                'system' => true,
                'is_used_in_grid' => true,
                'visible' => true,
                'is_visible_in_grid' => true,
                'data' => 'DataModel',
                'input_filter' => 'InputFilterClass',
                'multiline_count' => 3,
                'position' => -45,
                'validate_rules' => ['min_text_length' => 5],
            ],
            $this->builder->toArray()
        );
    }

    /**
     * @expectedException \Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException
     */
    public function testBuilderThrowsOnNonIntegerMultiLineCount()
    {
        $this->builder->withMultiLineCount('3');
    }

    /**
     * @expectedException \Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException
     */
    public function testBuilderThrowsOnNegativeMultiLineCount()
    {
        $this->builder->withMultiLineCount(-1);
    }

    public function testBuilderDoesNotThrowOnZeroMultiLineCount()
    {
        $this->builder->withMultiLineCount(0);
    }

    public function getMethodsThatChangeState()
    {
        return [
            ['filterableInGrid', []],
            ['searchableInGrid', [false]],
            ['system', []],
            ['usedInGrid', []],
            ['visible', []],
            ['visibleInGrid', []],
            ['withDataModel', ['DataModel']],
            ['withInputFilter', ['InputFilterClass']],
            ['withMultiLineCount', [3]],
            ['withSortOrder', [-45]],
            ['withValidationRules', [['min_text_length' => 5]]],
        ];
    }

    public function testAdditionalInformationIsMergedIntoMainConfiguration()
    {
        ;
        $this->builder = new CustomerConfiguration($this->mainConfiguration->required());
        $this->assertEquals(
            ['required' => true, 'system' => true],
            $this->builder->system()->toArray()
        );
    }
}
