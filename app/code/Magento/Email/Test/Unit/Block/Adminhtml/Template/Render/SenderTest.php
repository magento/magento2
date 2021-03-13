<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Render;

use Magento\Backend\Block\Context;
use Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SenderTest extends TestCase
{
    /**
     * @var Sender
     */
    private $block;

    /**
     * @var MockObject|Escaper
     */
    private $escaperMock;

    /**
     * Setup environment
     */
    protected function setUp(): void
    {
        $this->escaperMock = $this->createMock(Escaper::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getEscaper')->willReturn($this->escaperMock);

        $this->block = new Sender($contextMock);
    }

    /**
     * Test render() with sender name and sender email are not empty
     */
    public function testRenderWithSenderNameAndEmail()
    {
        $templateSenderEmail = 'test';
        $this->escaperMock->expects($this->any())->method('escapeHtml')->with($templateSenderEmail)
            ->willReturn($templateSenderEmail);
        $actualResult = $this->block->render(
            new DataObject(
                [
                    'template_sender_name' => 'test',
                    'template_sender_email' => 'test@localhost.com'
                ]
            )
        );
        $this->assertEquals('test [test@localhost.com]', $actualResult);
    }

    /**
     * Test render() with sender name and sender email are empty
     */
    public function testRenderWithNoSenderNameAndEmail()
    {
        $templateSenderEmail = '';
        $this->escaperMock->expects($this->any())->method('escapeHtml')->with($templateSenderEmail)
            ->willReturn($templateSenderEmail);
        $actualResult = $this->block->render(
            new DataObject(
                [
                    'template_sender_name' => '',
                    'template_sender_email' => ''
                ]
            )
        );
        $this->assertEquals('---', $actualResult);
    }
}
