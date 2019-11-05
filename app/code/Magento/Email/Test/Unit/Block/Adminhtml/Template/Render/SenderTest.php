<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Render;

use Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender;
use Magento\Framework\DataObject;

/**
 * Class \Magento\Email\Test\Unit\Block\Adminhtml\Template\Render\SenderTest
 */
class SenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Sender
     */
    protected $block;

    /**
     * Setup environment
     */
    protected function setUp()
    {
        $this->block = $this->getMockBuilder(Sender::class)
            ->disableOriginalConstructor()
            ->setMethods(['escapeHtml'])
            ->getMock();
    }

    /**
     * Test render() with sender name and sender email are not empty
     */
    public function testRenderWithSenderNameAndEmail()
    {
        $templateSenderEmail = 'test';
        $this->block->expects($this->any())->method('escapeHtml')->with($templateSenderEmail)
            ->willReturn('test');
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
        $this->block->expects($this->any())->method('escapeHtml')->with($templateSenderEmail)
            ->willReturn('');
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
