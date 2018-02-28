<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block\Adminhtml\Group\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Controller\RegistryConstants;

/**
 * Class GenericButtonTest
 *
 * Test for class \Magento\Customer\Block\Adminhtml\Group\Edit\GenericButton
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GenericButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Block\Adminhtml\Group\Edit\GenericButton
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
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\Customer\Block\Adminhtml\Group\Edit\GenericButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\GenericButton::getUrl
     */
    public function testGetUrl()
    {
        $url = "http://example.com/customer/group/";
        $route = 'button';
        $params = ['unit' => 'test'];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with($route, $params)
            ->willReturn($url);

        $this->assertEquals($url, $this->model->getUrl($route, $params));
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\GenericButton::getGroupId
     */
    public function testGetGroupId()
    {
        $groupId = 22;
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_GROUP_ID)
            ->willReturn($groupId);

        $this->assertEquals($groupId, $this->model->getGroupId());
    }

    /**
     * @return void
     * @covers \Magento\Customer\Block\Adminhtml\Group\Edit\GenericButton::getGroupId
     */
    public function testGetGroupIdWithoutGroup()
    {
        $this->assertNull($this->model->getGroupId());
    }
}
