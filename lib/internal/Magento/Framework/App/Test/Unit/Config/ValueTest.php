<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit\Config;

class ValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Value
     */
    protected $model;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheTypeListMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->cacheTypeListMock = $this->getMockBuilder(\Magento\Framework\App\Cache\TypeListInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Framework\App\Config\Value::class,
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
    public function testGetOldValue()
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue('old_value')
        );

        $this->assertEquals('old_value', $this->model->getOldValue());
    }

    /**
     * @param string $oldValue
     * @param string $value
     * @param bool $result
     * @dataProvider dataIsValueChanged
     */
    public function testIsValueChanged($oldValue, $value, $result)
    {
        $this->configMock->expects(
            $this->once()
        )->method(
            'getValue'
        )->with(
            null,
            'default'
        )->will(
            $this->returnValue($oldValue)
        );

        $this->model->setValue($value);

        $this->assertEquals($result, $this->model->isValueChanged());
    }

    /**
     * @return array
     */
    public function dataIsValueChanged()
    {
        return [
            ['value', 'value', false],
            ['value', 'new_value', true],
        ];
    }

    /**
     * @return void
     */
    public function testAfterLoad()
    {
        $this->eventManagerMock->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            'model_load_after',
            ['object' => $this->model]
        );
        $this->eventManagerMock->expects(
            $this->at(1)
        )->method(
            'dispatch'
        )->with(
            'config_data_load_after',
            [
                'data_object' => $this->model,
                'config_data' => $this->model,
            ]
        );

        $this->model->afterLoad();
    }

    /**
     * @param mixed $fieldsetData
     * @param string $key
     * @param string $result
     * @dataProvider dataProviderGetFieldsetDataValue
     * @return void
     */
    public function testGetFieldsetDataValue($fieldsetData, $key, $result)
    {
        $this->model->setData('fieldset_data', $fieldsetData);
        $this->assertEquals($result, $this->model->getFieldsetDataValue($key));
    }

    /**
     * @return array
     */
    public function dataProviderGetFieldsetDataValue()
    {
        return [
            [
                ['key' => 'value'],
                'key',
                'value',
            ],
            [
                ['key' => 'value'],
                'none',
                null,
            ],
            [
                'value',
                'key',
                null,
            ],
        ];
    }

    /**
     * @param int $callNumber
     * @param string $oldValue
     * @dataProvider afterSaveDataProvider
     */
    public function testAfterSave($callNumber, $oldValue)
    {
        $this->cacheTypeListMock->expects($this->exactly($callNumber))
            ->method('invalidate');
        $this->configMock->expects($this->any())
            ->method('getValue')
            ->willReturn($oldValue);
        $this->model->setValue('some_value');
        $this->assertInstanceOf(get_class($this->model), $this->model->afterSave());
    }

    public function afterSaveDataProvider()
    {
        return [
            [0, 'some_value'],
            [1, 'other_value'],
        ];
    }

    /**
     * @return void;
     */
    public function testAfterDelete()
    {
        $this->cacheTypeListMock->expects($this->once())->method('invalidate');
        $this->assertInstanceOf(get_class($this->model), $this->model->afterDelete());
    }
}
