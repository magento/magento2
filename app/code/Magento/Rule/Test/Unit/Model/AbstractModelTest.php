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
class AbstractModelTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Rule\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    private $model;

    /**
     * @var \Magento\Framework\Model\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    private $registryMock;

    /**
     * @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactoryMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $localeDateMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    protected function setUp()
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
            ->will($this->returnValue($this->eventManagerMock));

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
     * @return \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getSerializerMock()
    {
        $serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['serialize', 'unserialize'])
            ->getMock();

        $serializerMock->expects($this->any())
            ->method('serialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_encode($value);
                    }
                )
            );

        $serializerMock->expects($this->any())
            ->method('unserialize')
            ->will(
                $this->returnCallback(
                    function ($value) {
                        return json_decode($value, true);
                    }
                )
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

        $conditions->expects($this->once())->method('setRule')->will($this->returnSelf());
        $conditions->expects($this->once())->method('setId')->will($this->returnSelf());
        $conditions->expects($this->once())->method('setPrefix')->will($this->returnSelf());

        $this->model->expects($this->once())->method('getConditionsInstance')->will($this->returnValue($conditions));

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

        $actions->expects($this->once())->method('setRule')->will($this->returnSelf());
        $actions->expects($this->once())->method('setId')->will($this->returnSelf());
        $actions->expects($this->once())->method('setPrefix')->will($this->returnSelf());

        $this->model->expects($this->once())->method('getActionsInstance')->will($this->returnValue($actions));

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

        $conditions->expects($this->any())->method('asArray')->will($this->returnValue(['conditions' => 'array']));
        $actions->expects($this->any())->method('asArray')->will($this->returnValue(['actions' => 'array']));

        $this->eventManagerMock->expects($this->exactly(2))->method('dispatch');

        $this->assertEquals($this->model, $this->model->beforeSave());
        $this->assertEquals(json_encode(['conditions' => 'array']), $this->model->getConditionsSerialized());
        $this->assertEquals(json_encode(['actions' => 'array']), $this->model->getActionsSerialized());
    }
}
