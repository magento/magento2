<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Test\Unit\Block\Adminhtml\Bulk\Details;

use Magento\AsynchronousOperations\Block\Adminhtml\Bulk\Details\BackButton;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BackButtonTest extends TestCase
{
    /**
     * @var BackButton
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $urlBuilderMock;

    protected function setUp(): void
    {
        $this->urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $this->block = new BackButton(
            $this->urlBuilderMock
        );
    }

    public function testGetButtonData()
    {
        $backUrl = 'back url';
        $expectedResult = [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $backUrl),
            'class' => 'back',
            'sort_order' => 10
        ];

        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->with('*/')
            ->willReturn($backUrl);

        $this->assertEquals($expectedResult, $this->block->getButtonData());
    }
}
