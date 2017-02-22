<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender
 */
class SenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender
     */
    protected $sender;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->sender = $objectManager->getObject('Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender');
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderName()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateSenderName('Sender Name');
        $this->assertEquals('Sender Name ', $this->sender->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderEmail()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateSenderEmail('Sender Email');
        $this->assertEquals('[Sender Email]', $this->sender->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderNameAndEmail()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateSenderName('Sender Name');
        $row->setTemplateSenderEmail('Sender Email');
        $this->assertEquals('Sender Name [Sender Email]', $this->sender->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Sender::render
     */
    public function testRenderEmpty()
    {
        $row = new \Magento\Framework\DataObject();
        $this->assertEquals('---', $this->sender->render($row));
    }
}
