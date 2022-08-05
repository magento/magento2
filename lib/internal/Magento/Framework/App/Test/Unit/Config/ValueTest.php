<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Config;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ValueTest extends TestCase
{
    /**
     * @var Value
     */
    protected $model;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $configMock;

    /**
     * @var TypeListInterface|MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->cacheTypeListMock = $this->getMockBuilder(TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Value::class,
            [
                'config' => $this->configMock,
                'eventDispatcher' => $this->eventManagerMock,
                'cacheTypeList' => $this->cacheTypeListMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetOldValue(): void
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(null, 'default')
            ->willReturn('old_value');

        $this->assertEquals('old_value', $this->model->getOldValue());
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @param bool $result
     *
     * @return void
     * @dataProvider dataIsValueChanged
     */
    public function testIsValueChanged($oldValue, $value, $result): void
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with(null, 'default')
            ->willReturn($oldValue);

        $this->model->setValue($value);

        $this->assertEquals($result, $this->model->isValueChanged());
    }

    /**
     * @return array
     */
    public function dataIsValueChanged(): array
    {
        return [
            ['value', 'value', false],
            ['value', 'new_value', true]
        ];
    }

    /**
     * @return void
     */
    public function testAfterLoad(): void
    {
        $this->eventManagerMock
            ->method('dispatch')
            ->withConsecutive(
                [
                    'model_load_after',
                    ['object' => $this->model]
                ],
                [
                    'config_data_load_after',
                    [
                        'data_object' => $this->model,
                        'config_data' => $this->model
                    ]
                ]
            );

        $this->model->afterLoad();
    }

    /**
     * @param mixed $fieldsetData
     * @param string $key
     * @param string $result
     *
     * @return void
     * @dataProvider dataProviderGetFieldsetDataValue
     */
    public function testGetFieldsetDataValue($fieldsetData, $key, $result): void
    {
        $this->model->setData('fieldset_data', $fieldsetData);
        $this->assertEquals($result, $this->model->getFieldsetDataValue($key));
    }

    /**
     * @return array
     */
    public function dataProviderGetFieldsetDataValue(): array
    {
        return [
            [
                ['key' => 'value'],
                'key',
                'value'
            ],
            [
                ['key' => 'value'],
                'none',
                null
            ],
            [
                'value',
                'key',
                null
            ]
        ];
    }

    /**
     * @param int $callNumber
     * @param string $oldValue
     *
     * @return void
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($callNumber, $oldValue): void
    {
        $this->cacheTypeListMock->expects($this->exactly($callNumber))
            ->method('invalidate');
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($oldValue);
        $this->model->setValue('some_value');
        $this->assertInstanceOf(get_class($this->model), $this->model->afterSave());
    }

    /**
     * @return array
     */
    public function afterSaveDataProvider(): array
    {
        return [
            [0, 'some_value'],
            [1, 'other_value']
        ];
    }

    /**
     * @return void
     */
    public function testAfterDelete(): void
    {
        $this->cacheTypeListMock->expects($this->once())->method('invalidate');
        $this->assertInstanceOf(get_class($this->model), $this->model->afterDelete());
    }
}
