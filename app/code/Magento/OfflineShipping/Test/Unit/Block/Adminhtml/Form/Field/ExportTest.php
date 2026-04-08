<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Escaper;
use Magento\Framework\View\Layout;
use Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

class ExportTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Export
     */
    protected $_object;

    /**
     * @var UrlInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $backendUrl;

    protected function setUp(): void
    {
        $this->backendUrl = $this->createMock(UrlInterface::class);
        $this->backendUrl->expects($this->once())->method('getUrl')->with("*/*/exportTablerates", ['website' => 1]);

        $this->_object = $this->getMockBuilder(Export::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new \ReflectionClass(Export::class);
        $backendUrlProperty = $reflection->getProperty('_backendUrl');
        $backendUrlProperty->setValue($this->_object, $this->backendUrl);
    }

    public function testGetElementHtml()
    {
        $expected = 'some test data';

        $form = $this->createPartialMockWithReflection(Form::class, ['getParent']);
        $parentObjectMock = $this->createPartialMock(Template::class, ['getLayout']);
        $layoutMock = $this->createMock(Layout::class);

        $blockMock = $this->createMock(Button::class);

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->expects($this->once())->method('getParam')->with('website')->willReturn(1);

        $mockData = $this->createPartialMockWithReflection(\stdClass::class, ['toHtml']);
        $mockData->expects($this->once())->method('toHtml')->willReturn($expected);

        $blockMock->expects($this->once())->method('getRequest')->willReturn($requestMock);
        $blockMock->expects($this->any())->method('setData')->willReturn($mockData);

        $layoutMock->expects($this->once())->method('createBlock')->willReturn($blockMock);
        $parentObjectMock->expects($this->once())->method('getLayout')->willReturn($layoutMock);
        $form->expects($this->once())->method('getParent')->willReturn($parentObjectMock);

        $this->_object->setForm($form);
        $this->assertEquals($expected, $this->_object->getElementHtml());
    }
}
