<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Block\Adminhtml\Edit;

use Magento\CatalogRule\Controller\RegistryConstants;

class DeleteButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\CatalogRule\Block\Adminhtml\Edit\DeleteButton
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

    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = new \Magento\CatalogRule\Block\Adminhtml\Edit\DeleteButton(
            $contextMock,
            $this->registryMock
        );
    }

    public function testGetButtonData()
    {
        $ruleId = 42;
        $deleteUrl = 'http://magento.com/rule/delete/' . $ruleId;
        $ruleMock = new \Magento\Framework\DataObject(['id' => $ruleId]);

        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with(RegistryConstants::CURRENT_CATALOG_RULE_ID)
            ->willReturn($ruleMock);
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/delete', ['id' => $ruleId])
            ->willReturn($deleteUrl);

        $data = [
            'label' => __('Delete Rule'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\'' . __(
                'Are you sure you want to do this?'
            ) . '\', \'' . $deleteUrl . '\', {data: {}})',
            'sort_order' => 20,
        ];

        $this->assertEquals($data, $this->model->getButtonData());
    }

    public function testGetButtonDataWithoutRule()
    {
        $this->assertEquals([], $this->model->getButtonData());
    }
}
