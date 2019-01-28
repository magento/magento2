<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type
 */
class TypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type
     */
    protected $type;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->type = $objectManager->getObject(\Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::class);
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderHtml()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML);
        $this->assertSame('HTML', $this->type->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderText()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT);
        $this->assertSame('Text', $this->type->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderUnknown()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateType('xx');
        $this->assertSame('Unknown', $this->type->render($row));
    }
}
