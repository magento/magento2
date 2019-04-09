<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group as ResourceGroup;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filter\Translit;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class GroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Group
     */
    private $model;

    /**
     * @var ResourceGroup|MockObject
     */
    private $resourceMock;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->resourceMock = $this->createMock(ResourceGroup::class);
        $translitFilter = $this->getMockBuilder(Translit::class)
            ->disableOriginalConstructor()
            ->getMock();
        $translitFilter->expects($this->atLeastOnce())->method('filter')->willReturnArgument(0);

        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
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
            Group::class,
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
            ['configurable', md5('configurable')],
            ['configurAble', md5('configurable')],
            ['///', md5('///')],
        ];
    }
}
