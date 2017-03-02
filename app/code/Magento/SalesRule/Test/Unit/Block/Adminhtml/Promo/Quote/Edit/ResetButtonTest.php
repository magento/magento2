<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ResetButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\ResetButton
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
        $this->urlBuilderMock = $this->getMock(\Magento\Framework\UrlInterface::class, [], [], '', false);
        $this->registryMock = $this->getMock(\Magento\Framework\Registry::class, [], [], '', false);
        $contextMock = $this->getMock(\Magento\Backend\Block\Widget\Context::class, [], [], '', false);

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\ResetButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    public function testGetButtonData()
    {
        $data = [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 30,
        ];

        $this->assertEquals($data, $this->model->getButtonData());
    }
}
