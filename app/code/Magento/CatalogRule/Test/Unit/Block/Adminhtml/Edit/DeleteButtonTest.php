<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Block\Adminhtml\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\CatalogRule\Block\Adminhtml\Edit\DeleteButton;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
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

        $this->model = new DeleteButton(
            $contextMock,
            $this->registryMock
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
