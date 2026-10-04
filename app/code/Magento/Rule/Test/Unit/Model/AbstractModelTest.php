<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
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
    use MockCreationTrait;

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
        $this->localeDateMock = $this->createMock(TimezoneInterface::class);

        $this->formFactoryMock = $this->createMock(FormFactory::class);

        $this->registryMock = $this->createMock(Registry::class);

        $this->contextMock = $this->createPartialMock(Context::class, ['getEventDispatcher']);

        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);

        $resourceMock = $this->createMock(AbstractResource::class);
        $resourceCollectionMock = $this->createMock(AbstractDb::class);
        $extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $customAttributeFactory = $this->createMock(AttributeValueFactory::class);

        $this->model = $this->createPartialMock(AbstractModel::class, ['getConditionsInstance', 'getActionsInstance']);
        
        $constructorArgs = [
            $this->contextMock,
            $this->registryMock,
            $this->formFactoryMock,
            $this->localeDateMock,
            $resourceMock,
            $resourceCollectionMock,
            [],
            $extensionFactory,
            $customAttributeFactory,
            $this->getSerializerMock(),
        ];
        
        $this->model->__construct(...$constructorArgs);
    }

    /**
     * Get mock for serializer
     *
     * @return Json|MockObject
     */
    private function getSerializerMock()
    {
        $serializerMock = $this->createPartialMock(Json::class, ['serialize', 'unserialize']);

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
        $conditions = $this->createPartialMockWithReflection(
            Combine::class,
            ['loadArray', 'setRule', 'setId', 'setPrefix']
        );

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
        $actions = $this->createPartialMockWithReflection(
            Collection::class,
            ['loadArray', 'setRule', 'setId', 'setPrefix']
        );

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
        $conditions = $this->createPartialMock(Combine::class, ['asArray']);

        $actions = $this->createPartialMock(Collection::class, ['asArray']);

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
