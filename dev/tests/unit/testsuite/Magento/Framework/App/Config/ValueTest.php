<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

class ValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Config\Value
     */
    protected $model;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface');

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Framework\App\Config\Value',
            [
                'config' => $this->configMock,
                'eventDispatcher' => $this->eventManager,
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
        $this->eventManager->expects(
            $this->at(0)
        )->method(
            'dispatch'
        )->with(
            'model_load_after',
            ['object' => $this->model]
        );
        $this->eventManager->expects(
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
}
