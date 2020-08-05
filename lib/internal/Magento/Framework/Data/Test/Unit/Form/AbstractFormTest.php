<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Form;

use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractFormTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $factoryElementMock;

    /**
     * @var MockObject
     */
    protected $factoryCollectionMock;

    /**
     * @var MockObject
     */
    protected $elementMock;

    /**
     * @var MockObject
     */
    protected $allElementsMock;

    /**
     * @var AbstractForm
     */
    protected $abstractForm;

    protected function setUp(): void
    {
        $this->factoryElementMock =
            $this->createPartialMock(Factory::class, ['create']);
        $this->factoryCollectionMock =
            $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->allElementsMock =
            $this->createMock(Collection::class);
        $this->elementMock =
            $this->createMock(AbstractElement::class);

        $this->abstractForm = new AbstractForm($this->factoryElementMock, $this->factoryCollectionMock, []);
    }

    public function testAddElement()
    {
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->allElementsMock);
        $this->elementMock->expects($this->once())->method('setForm');
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->abstractForm->addElement($this->elementMock, false);
    }

    public function testAddField()
    {
        $config = ['name' => 'store_type', 'no_span' => true, 'value' => 'value'];
        $this->factoryElementMock
            ->expects($this->once())
            ->method('create')
            ->with('hidden', ['data' => $config])
            ->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setId')->with('store_type');
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->allElementsMock);
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->assertEquals($this->elementMock, $this->abstractForm->addField('store_type', 'hidden', $config));
        $this->abstractForm->removeField('hidden');
    }

    public function testAddFieldset()
    {
        $config = ['name' => 'store_type', 'no_span' => true, 'value' => 'value'];
        $this->factoryElementMock
            ->expects($this->once())
            ->method('create')
            ->with('fieldset', ['data' => $config])
            ->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setId')->with('hidden');
        $this->elementMock->expects($this->once())->method('setAdvanced')->with(false);
        $this->elementMock->expects($this->once())->method('setForm');
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->allElementsMock);
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->abstractForm->addFieldset('hidden', $config);
    }

    public function testAddColumn()
    {
        $config = ['name' => 'store_type', 'no_span' => true, 'value' => 'value'];
        $this->factoryElementMock
            ->expects($this->once())
            ->method('create')
            ->with('column', ['data' => $config])
            ->willReturn($this->elementMock);
        $this->elementMock->expects($this->once())->method('setId')->with('hidden');
        $this->elementMock->expects($this->exactly(2))->method('setForm')->willReturnSelf();
        $this->factoryCollectionMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->allElementsMock);
        $this->allElementsMock->expects($this->once())->method('add')->with($this->elementMock, false);
        $this->abstractForm->addColumn('hidden', $config);
    }

    public function testAddCustomAttribute()
    {
        $this->assertEquals(
            $this->abstractForm,
            $this->abstractForm->addCustomAttribute('attribute_key1', 'attribute_value1')
        );

        $form = clone $this->abstractForm;
        $this->assertNotEquals(
            $form,
            $this->abstractForm->addCustomAttribute('attribute_key2', 'attribute_value2')
        );
    }

    /**
     * @param array $keys
     * @param array $data
     * @param array $customAttributes
     * @param string $result
     * @dataProvider dataProviderSerialize
     */
    public function testSerialize(
        $keys,
        $data,
        $customAttributes,
        $result
    ) {
        foreach ($data as $key => $value) {
            $this->abstractForm->setData($key, $value);
        }

        foreach ($customAttributes as $key => $value) {
            $this->abstractForm->addCustomAttribute($key, $value);
        }

        $this->assertEquals($result, $this->abstractForm->serialize($keys));
    }

    /**
     * 1. Keys
     * 2. Data
     * 3. Custom Attributes
     * 4. Result
     *
     * @return array
     */
    public function dataProviderSerialize()
    {
        return [
            [[], [], [], ''],
            [['key1'], [], [], ''],
            [['key1'], ['key1' => 'value'], [], 'key1="value"'],
            [['key1', 'key2'], ['key1' => 'value'], [], 'key1="value"'],
            [['key1', 'key2'], ['key1' => 'value', 'key3' => 'value3'], [], 'key1="value"'],
            [['key1', 'key2'], ['key1' => 'value', 'key3' => 'value3'], ['custom1' => ''], 'key1="value"'],
            [
                [
                    'key1',
                    'key2',
                ],
                [
                    'key1' => 'value',
                    'key3' => 'value3',
                ],
                [
                    'custom1' => 'custom_value1',
                ],
                'key1="value" custom1="custom_value1"'
            ],
            [
                [
                    'key1',
                    'key2',
                ],
                [
                    'key1' => 'value',
                    'key3' => 'value3',
                ],
                [
                    'custom1' => 'custom_value1',
                    'custom2' => '',
                    'custom3' => 0,
                    'custom4' => false,
                    'custom5' => null,
                ],
                'key1="value" custom1="custom_value1"'
            ],
        ];
    }
}
