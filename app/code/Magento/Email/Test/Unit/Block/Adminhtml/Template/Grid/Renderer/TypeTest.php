<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type
     */
    protected $type;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->type = $objectManager->getObject('Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type');
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderHtml()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_HTML);
        $this->assertEquals('HTML', $this->type->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderText()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateType(\Magento\Framework\App\TemplateTypesInterface::TYPE_TEXT);
        $this->assertEquals('Text', $this->type->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderUnknown()
    {
        $row = new \Magento\Framework\DataObject();
        $row->setTemplateType('xx');
        $this->assertEquals('Unknown', $this->type->render($row));
    }
}
