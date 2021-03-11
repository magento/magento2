<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SaveButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\SaveButton
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $registryMock;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\SaveButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    public function testGetButtonData()
    {
        $data = [
            'label' => __('Save'),
            'class' => 'save primary',
            'on_click' => '',
        ];

        $this->assertEquals($data, $this->model->getButtonData());
    }
}
