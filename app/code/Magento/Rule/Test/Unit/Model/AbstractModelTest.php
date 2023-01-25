<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Rule\Test\Unit\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Magento\Rule\Model\Action\Collection;
use Magento\Rule\Model\Condition\Combine;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractModelTest. Unit test for \Magento\Rule\Model\AbstractModel
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractModelTest extends TestCase
{

    /**
     * @var AbstractModel|MockObject
     */
    private $model;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactoryMock;

    /**
     * @var TimezoneInterface|MockObject
     */
    private $localeDateMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->localeDateMock = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->formFactoryMock = $this->getMockBuilder(FormFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEventDispatcher'])
            ->getMock();

        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['dispatch'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $resourceMock = $this->getMockBuilder(AbstractResource::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceCollectionMock = $this->getMockBuilder(AbstractDb::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extensionFactory = $this->getMockBuilder(ExtensionAttributesFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customAttributeFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $this->getMockForAbstractClass(
            AbstractModel::class,
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
     * @return Json|MockObject
     */
    private function getSerializerMock()
    {
        $serializerMock = $this->getMockBuilder(Json::class)
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
        $conditions = $this->getMockBuilder(Combine::class)
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
        $actions = $this->getMockBuilder(Collection::class)
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
        $conditions = $this->getMockBuilder(Combine::class)
            ->setMethods(['asArray'])
            ->disableOriginalConstructor()
            ->getMock();

        $actions = $this->getMockBuilder(Collection::class)
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
