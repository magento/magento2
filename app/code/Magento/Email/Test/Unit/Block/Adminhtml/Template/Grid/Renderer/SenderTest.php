<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender
 */
class SenderTest extends TestCase
{
    /**
     * @var Sender
     */
    protected $sender;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(
            Escaper::class
        );
        $this->sender = $objectManager->getObject(
            Sender::class,
            [
                'escaper' => $escaper
            ]
        );
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderName()
    {
        $row = new DataObject();
        $row->setTemplateSenderName('Sender Name');
        $this->assertEquals('Sender Name ', $this->sender->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderEmail()
    {
        $row = new DataObject();
        $row->setTemplateSenderEmail('Sender Email');
        $this->assertEquals('[Sender Email]', $this->sender->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderNameAndEmail()
    {
        $row = new DataObject();
        $row->setTemplateSenderName('Sender Name');
        $row->setTemplateSenderEmail('Sender Email');
        $this->assertEquals('Sender Name [Sender Email]', $this->sender->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderEmpty()
    {
        $row = new DataObject();
        $this->assertEquals('---', $this->sender->render($row));
    }
}
