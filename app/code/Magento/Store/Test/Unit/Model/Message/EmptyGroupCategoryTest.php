<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Message;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Store\Model\Message\EmptyGroupCategory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\Group;

class EmptyGroupCategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManagerHelper;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var EmptyGroupCategory
     */
    private $model;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManagerHelper = new ObjectManager($this);

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();

        $this->model = new EmptyGroupCategory(
            $this->collectionMock,
            $this->urlBuilderMock
        );
    }

    /**
     * @param boolean $expected
     * @param array $items
     * @dataProvider isDisplayedDataProvider
     */
    public function testIsDisplayed($expected, array $items)
    {
        $this->collectionMock->expects($this->once())
            ->method('setWithoutAssignedCategoryFilter')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
        $this->assertEquals($expected, $this->model->isDisplayed());
    }

    public function testGetIdentity()
    {
        $this->assertEquals($this->model->getIdentity(), 'empty_assigned_group_category');
    }

    public function testGetText()
    {
        $groupMock1 = $this->getGroupMock(1, 'groupName1');
        $groupMock2 = $this->getGroupMock(2, 'groupName2');

        $this->collectionMock->expects($this->once())
            ->method('setWithoutAssignedCategoryFilter')
            ->willReturnSelf();
        $this->collectionMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$groupMock1, $groupMock2]);
        $this->urlBuilderMock->expects($this->exactly(2))
            ->method('getUrl')
            ->withConsecutive(
                ['adminhtml/system_store/editGroup', ['group_id' => 1]],
                ['adminhtml/system_store/editGroup', ['group_id' => 2]]
            )
            ->willReturnOnConsecutiveCalls(
                'http://url1.com',
                'http://url2.com'
            );

        $this->assertEquals(
            'The following stores are not associated with a root category: <a href="http://url1.com">groupName1</a>, '
            . '<a href="http://url2.com">groupName2</a>. For the store to be displayed in the storefront, '
            . 'it must be associated with a root category.',
            $this->model->getText()->getText()
        );
    }

    /**
     * Creates MockObject for Group class.
     *
     * @param integer $id
     * @param string $name
     * @return MockObject
     */
    private function getGroupMock($id, $name)
    {
        $groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupMock->expects($this->once())
            ->method('getId')
            ->willReturn($id);
        $groupMock->expects($this->once())
            ->method('getName')
            ->willReturn($name);

        return $groupMock;
    }

    public function testGetSeverity()
    {
        $this->assertEquals($this->model->getSeverity(), 2);
    }

    /**
     * @return array
     */
    public function isDisplayedDataProvider()
    {
        return [
            [
                false,
                []
            ],
            [
                true,
                ['test']
            ]
        ];
    }
}
