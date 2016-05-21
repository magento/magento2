<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Test\Unit\Model\AttributeConfiguration;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\AttributeConfiguration\AdditionalConfigurationInterface;
use Magento\Eav\Setup\AttributeConfiguration\Provider\ProviderInterface;
use Magento\Eav\Setup\AttributeConfiguration\Provider\ScopeProvider;
use Magento\Eav\Setup\AttributeConfiguration\MainConfiguration;

class MainConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MainConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var ProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inputTypeProviderMock;

    /**
     * @var AdditionalConfigurationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $additionalConfiguration;

    protected function setUp()
    {
        $this->inputTypeProviderMock = $this->getMockBuilder(ProviderInterface::class)
                                            ->setMethods(['resolve'])
                                            ->getMockForAbstractClass();

        $this->builder = new MainConfiguration($this->inputTypeProviderMock, new ScopeProvider());

        $this->additionalConfiguration = $this->getMockBuilder(AdditionalConfigurationInterface::class)
                                              ->setMethods(['toArray'])
                                              ->getMockForAbstractClass();
    }

    public function testBuilderReturnsACompatibleArrayAndChangingStateReturnsANewInstance()
    {
        $this->inputTypeProviderMock
             ->expects($this->once())
             ->method('resolve')
             ->with('frontend_input_text')
             ->willReturnArgument(0);

        $builder = $this->builder;

        foreach ($this->getMethodsThatChangeState() as $methodInfo) {
            $this->builder = call_user_func_array([$this->builder, $methodInfo[0]], $methodInfo[1]);
            $this->assertNotSame($builder, $this->builder);
        }

        $this->assertEquals(
            [
                'sort_order' => 13,
                'group' => 'group',
                'user_defined' => false,
                'backend' => 'backendModel',
                'type' => 'backendType',
                'table' => 'backendTable',
                'frontend' => 'frontendModel',
                'input' => 'frontend_input_text',
                'label' => 'label',
                'frontend_class' => 'class1 class2',
                'attribute_model' => 'attrModel',
                'source' => 'sourceModel',
                'required' => true,
                'default' => 'defaultValue',
                'unique' => true,
                'note' => 'note',
                'global' => ScopedAttributeInterface::SCOPE_STORE,
                'option' => ['options'],
                'custom_option' => 'custom_value',
            ],
            $this->builder->toArray()
        );
    }

    public function getMethodsThatChangeState()
    {
        return [
            ['unique', []],
            ['userDefined', [false]],
            ['required', []],
            ['withCustomConfiguration', [['custom_option' => 'custom_value']]],
            ['withBackendModel', ['backendModel']],
            ['withBackendTable', ['backendTable']],
            ['withBackendType', ['backendType']],
            ['withAttributeModel', ['attrModel']],
            ['withDefaultValue', ['defaultValue']],
            ['withFrontendCssClasses', [['class1', 'class2']]],
            ['withFrontendInput', ['frontend_input_text']],
            ['withFrontendLabel', ['label']],
            ['withFrontendModel', ['frontendModel']],
            ['withGroup', ['group']],
            ['withNote', ['note']],
            ['withOptions', [['options']]],
            ['withStoreScope', []],
            ['withSortOrder', [13]],
            ['withSourceModel', ['sourceModel']],
        ];
    }

    public function testAdditionalInformationIsMergedIntoMainConfiguration()
    {
        $this->additionalConfiguration
             ->expects($this->once())
             ->method('toArray')
             ->willReturn(['key1' => 'value1', 'key2' => 'value2']);

        $builder = $this->builder->withAdditionalConfiguration($this->additionalConfiguration);

        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $builder->toArray());
    }

    public function testAdditionalOptionsCanBeOverwrittenWithACustomOptionsCall()
    {
        $this->additionalConfiguration
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(['key1' => 'value1', 'key2' => 'value2']);

        $builder = $this->builder->withAdditionalConfiguration($this->additionalConfiguration)
                                 ->withCustomConfiguration(['key1' => 'override', 'key3' => 'value3']);

        $this->assertEquals(
            ['key1' => 'override', 'key2' => 'value2', 'key3' => 'value3'],
            $builder->toArray()
        );
    }

    public function testCustomOptionsCannotBeOverwrittenWithAnAdditionalOptionsCall()
    {
        $this->additionalConfiguration
            ->expects($this->once())
            ->method('toArray')
            ->willReturn(['key1' => 'override', 'key2' => 'value2']);

        $builder = $this->builder->withCustomConfiguration(['key1' => 'value1', 'key3' => 'value3'])
                                 ->withAdditionalConfiguration($this->additionalConfiguration);

        $this->assertEquals(
            ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'],
            $builder->toArray()
        );
    }
}
