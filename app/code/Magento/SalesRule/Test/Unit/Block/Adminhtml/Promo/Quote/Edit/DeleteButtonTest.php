<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\SalesRule\Model\RegistryConstants;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DeleteButtonTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\DeleteButton
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
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $this->registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $contextMock = $this->createMock(\Magento\Backend\Block\Widget\Context::class);

        $contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\DeleteButton::class,
            [
                'context' => $contextMock,
                'registry' => $this->registryMock
            ]
        );
    }

    /**
     * Test empty response without a present rule.
     */
    public function testGetButtonDataWithoutRule()
    {
        $this->assertEquals([], $this->model->getButtonData());
    }
}
