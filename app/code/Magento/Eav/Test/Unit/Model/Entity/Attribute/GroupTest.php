<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\Group as AttributeGroup;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group as AttributeGroupResourceModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filter\Translit;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    /**
     * @var Group
     */
    private $model;

    /**
     * @var MockObject
     */
    private $resourceMock;

    /**
     * @var MockObject
     */
    private $eventManagerMock;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(AttributeGroupResourceModel::class);
        $translitFilter = $this->getMockBuilder(Translit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translitFilter->expects($this->atLeastOnce())->method('filter')->willReturnArgument(0);

        $this->eventManagerMock = $this->getMockForAbstractClass(ManagerInterface::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->any())->method('getEventDispatcher')->willReturn($this->eventManagerMock);
        $constructorArguments = [
            'resource' => $this->resourceMock,
            'translitFilter' => $translitFilter,
            'context' => $contextMock,
            'reservedSystemNames' => ['configurable'],
        ];
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            AttributeGroup::class,
            $constructorArguments
        );
    }

    /**
     * @dataProvider attributeGroupCodeDataProvider
     * @param string $groupName
     * @param string $groupCode
     */
    public function testBeforeSaveGeneratesGroupCodeBasedOnGroupName($groupName, $groupCode)
    {
        $this->model->setAttributeGroupName($groupName);
        $this->model->beforeSave();
        $this->assertEquals($groupCode, $this->model->getAttributeGroupCode());
    }

    /**
     * @return array
     */
    public function attributeGroupCodeDataProvider()
    {
        return [
            ['General Group', 'general-group'],
            ['configurable', hash('md5', 'configurable')],
            ['configurAble', hash('md5', 'configurable')],
            ['///', hash('md5', '///')],
        ];
    }
}
