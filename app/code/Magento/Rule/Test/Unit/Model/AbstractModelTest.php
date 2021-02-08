<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Rule\Test\Unit\Model;

/**
 * Class AbstractModelTest. Unit test for \Magento\Rule\Model\AbstractModel
 *
 * @package Magento\Rule\Test\Unit\Model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractModelTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Rule\Model\AbstractModel|\PHPUnit\Framework\MockObject\MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    private $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $formFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeDateMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->localeDateMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactoryMock = $this->getMockBuilder(\Magento\Framework\Data\FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(\Magento\Framework\Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\Model\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $resourceMock = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Collection\AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionFactory = $this->getMockBuilder(\Magento\Framework\Api\ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customAttributeFactory = $this->getMockBuilder(\Magento\Framework\Api\AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->getMockForAbstractClass(
            \Magento\Rule\Model\AbstractModel::class,
            [
                'context' => $this->contextMock,
                'registry' => $this->registryMock,
                'formFactory' => $this->formFactoryMock,
                'localeDate' => $this->localeDateMock,
                'resource' => $resourceMock,
                'resourceCollection' => $resourceCollectionMock,
                'data' => [],
                'extensionFactory' => $extensionFactory,
                'customAttributeFactory' => $customAttributeFactory,
                'serializer' => $this->getSerializerMock(),
            ]
        );
    }

    /**
     * Get mock for serializer
     *
     * @return \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getSerializerMock()
    {
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $serializerMock->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                
                    function ($value) {
                        return json_encode($value);
                    }
                
            );

        $serializerMock->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                
                    function ($value) {
                        return json_decode($value, true);
                    }
                
            );

        return $serializerMock;
    }

    public function testGetConditions()
    {
        $conditionsArray = ['conditions' => 'serialized'];
        $serializedConditions = json_encode($conditionsArray);
        $conditions = $this->getMockBuilder(\Magento\Rule\Model\Condition\Combine::class)
            ->setMethods(['setRule', 'setId', 'setPrefix', 'loadArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $conditions->expects($this->once())->method('setRule')->willReturnSelf();
        $conditions->expects($this->once())->method('setId')->willReturnSelf();
        $conditions->expects($this->once())->method('setPrefix')->willReturnSelf();

        $this->model->expects($this->once())->method('getConditionsInstance')->willReturn($conditions);

        $this->model->setConditionsSerialized($serializedConditions);

        $conditions->expects($this->once())->method('loadArray')->with($conditionsArray);

        $this->assertEquals($conditions, $this->model->getConditions());
    }

    public function testGetActions()
    {
        $actionsArray = ['actions' => 'some_actions'];
        $actionsSerialized = json_encode($actionsArray);
        $actions = $this->getMockBuilder(\Magento\Rule\Model\Action\Collection::class)
            ->setMethods(['setRule', 'setId', 'setPrefix', 'loadArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $actions->expects($this->once())->method('setRule')->willReturnSelf();
        $actions->expects($this->once())->method('setId')->willReturnSelf();
        $actions->expects($this->once())->method('setPrefix')->willReturnSelf();

        $this->model->expects($this->once())->method('getActionsInstance')->willReturn($actions);

        $this->model->setActionsSerialized($actionsSerialized);

        $actions->expects($this->once())->method('loadArray')->with($actionsArray);

        $this->assertEquals($actions, $this->model->getActions());
    }

    public function testBeforeSave()
    {
        $conditions = $this->getMockBuilder(\Magento\Rule\Model\Condition\Combine::class)
            ->setMethods(['asArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $actions = $this->getMockBuilder(\Magento\Rule\Model\Action\Collection::class)
            ->setMethods(['asArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model->setConditions($conditions);
        $this->model->setActions($actions);

        $conditions->expects($this->any())->method('asArray')->willReturn(['conditions' => 'array']);
        $actions->expects($this->any())->method('asArray')->willReturn(['actions' => 'array']);

        $this->eventManagerMock->expects($this->exactly(2))->method('dispatch');

        $this->assertEquals($this->model, $this->model->beforeSave());
        $this->assertEquals(json_encode(['conditions' => 'array']), $this->model->getConditionsSerialized());
        $this->assertEquals(json_encode(['actions' => 'array']), $this->model->getActionsSerialized());
    }
}
