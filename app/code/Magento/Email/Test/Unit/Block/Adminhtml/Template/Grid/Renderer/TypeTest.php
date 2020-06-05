<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Email\Test\Unit\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type;
use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type
 */
class TypeTest extends TestCase
{
    /**
     * @var Type
     */
    protected $type;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->type = $objectManager->getObject(Type::class);
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderHtml()
    {
        $row = new DataObject();
        $row->setTemplateType(TemplateTypesInterface::TYPE_HTML);
        $this->assertEquals('HTML', $this->type->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderText()
    {
        $row = new DataObject();
        $row->setTemplateType(TemplateTypesInterface::TYPE_TEXT);
        $this->assertEquals('Text', $this->type->render($row));
    }

    /**
     * @covers \Magento\Email\Block\Adminhtml\Template\Grid\Renderer\Type::render
     */
    public function testRenderUnknown()
    {
        $row = new DataObject();
        $row->setTemplateType('xx');
        $this->assertEquals('Unknown', $this->type->render($row));
    }
}
