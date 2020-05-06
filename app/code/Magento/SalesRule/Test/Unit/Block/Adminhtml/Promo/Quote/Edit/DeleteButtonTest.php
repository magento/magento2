<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Block\Adminhtml\Promo\Quote\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\DeleteButton;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeleteButtonTest extends TestCase
{
    /**
     * @var DeleteButton
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->registryMock = $this->createMock(Registry::class);
        $contextMock = $this->createMock(Context::class);

        $contextMock->expects($this->any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->model = (new ObjectManager($this))->getObject(
            DeleteButton::class,
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
