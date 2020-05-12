<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Variable\Test\Unit\Model\Variable;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Variable\Model\ResourceModel\Variable\Collection as VariableCollection;
use Magento\Variable\Model\ResourceModel\Variable\CollectionFactory as VariableCollectionFactory;
use Magento\Variable\Model\Source\Variables as StoreVariables;
use Magento\Variable\Model\Variable\Data as VariableDataModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var VariableDataModel
     */
    private $model;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var StoreVariables|MockObject
     */
    private $storesVariablesMock;

    /**
     * @var VariableCollectionFactory|MockObject
     */
    private $variableCollectionFactoryMock;

    /**
     * Set up before tests
     */
    protected function setUp(): void
    {
        $this->storesVariablesMock = $this->createMock(StoreVariables::class);
        $this->variableCollectionFactoryMock = $this->getMockBuilder(
            VariableCollectionFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            VariableDataModel::class,
            [
                'collectionFactory' => $this->variableCollectionFactoryMock,
                'storesVariables' => $this->storesVariablesMock
            ]
        );
    }

    /**
     * Test getDefaultVariables() function
     */
    public function testGetDefaultVariables()
    {
        $storesVariablesData = [
            [
                'value' => 'test 1',
                'label' => 'Test Label 1',
                'group_label' => 'Group Label 1'
            ],
            [
                'value' => 'test 2',
                'label' => 'Test Label 2',
                'group_label' => 'Group Label 2'
            ]
        ];
        $expectedResult = [
            [
                'code' => 'test 1',
                'variable_name' => 'Group Label 1 / Test Label 1',
                'variable_type' => StoreVariables::DEFAULT_VARIABLE_TYPE
            ],
            [
                'code' => 'test 2',
                'variable_name' => 'Group Label 2 / Test Label 2',
                'variable_type' => StoreVariables::DEFAULT_VARIABLE_TYPE
            ]
        ];
        $this->storesVariablesMock->expects($this->any())->method('getData')->willReturn($storesVariablesData);

        $this->assertEquals($expectedResult, $this->model->getDefaultVariables());
    }

    /**
     * Test getCustomVariables() function
     */
    public function testGetCustomVariables()
    {
        $customVariables = [
            [
                'code' => 'test 1',
                'name' => 'Test 1'
            ],
            [
                'code' => 'test 2',
                'name' => 'Test 2'
            ]
        ];
        $expectedResult = [
            [
                'code' => 'test 1',
                'variable_name' => 'Custom Variable / Test 1',
                'variable_type' => StoreVariables::CUSTOM_VARIABLE_TYPE
            ],
            [
                'code' => 'test 2',
                'variable_name' => 'Custom Variable / Test 2',
                'variable_type' => StoreVariables::CUSTOM_VARIABLE_TYPE
            ]
        ];
        $variableCollectionMock = $this->createMock(VariableCollection::class);
        $this->variableCollectionFactoryMock->expects($this->once())->method('create')
            ->willReturn($variableCollectionMock);
        $variableCollectionMock->expects($this->any())->method('getData')->willReturn($customVariables);

        $this->assertEquals($expectedResult, $this->model->getCustomVariables());
    }
}
