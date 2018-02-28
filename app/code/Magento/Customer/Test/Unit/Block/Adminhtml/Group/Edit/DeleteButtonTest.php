<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Group\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class DeleteButtonTest
 *
 * Test for class \Magento\Customer\Block\Adminhtml\Group\Edit\DeleteButton
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DeleteButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Group\Edit\DeleteButton
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \Magento\Customer\Api\GroupManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupManagement;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $this->groupManagement = $this->createMock(\Magento\Customer\Api\GroupManagementInterface::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Customer\Block\Adminhtml\Group\Edit\DeleteButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock,
                'groupManagement' => $this->groupManagement
            ]
        );
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\DeleteButton::getButtonData
     */
    public function testGetButtonData()
    {
        $groupId = 22;
        $deleteUrl = 'http://example.com/customer/group/' . $groupId;
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_GROUP_ID)
            ->willReturn($groupId);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/delete', ['id' => $groupId])
            ->willReturn($deleteUrl);

        $this->groupManagement->expects($this->once())
            ->method('isReadonly')
            ->with($groupId)
            ->willReturn(false);

        $data = [
            'label' => __('Delete Customer Group'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $deleteUrl . '\')',
            'sort_order' => 20,
        ];

        $this->assertEquals($data, $this->model->getButtonData());
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\DeleteButton::getButtonData
     */
    public function testGetButtonDataWithoutGroup()
    {
        $this->assertEquals([], $this->model->getButtonData());
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\DeleteButton::getButtonData
     */
    public function testGetButtonDataWithReadonlyGroup()
    {
        $groupId = 22;
        $this->registryMock->expects($this->any())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_GROUP_ID)
            ->willReturn($groupId);

        $this->groupManagement->expects($this->once())
            ->method('isReadonly')
            ->with($groupId)
            ->willReturn(true);

        $this->assertEquals([], $this->model->getButtonData());
    }
}
