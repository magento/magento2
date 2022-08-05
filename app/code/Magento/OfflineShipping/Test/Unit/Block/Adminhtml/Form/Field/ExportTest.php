<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\OfflineShipping\Test\Unit\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\OfflineShipping\Block\Adminhtml\Form\Field\Export;
use PHPUnit\Framework\TestCase;

class ExportTest extends TestCase
{
    /**
     * @var Export
     */
    protected $_object;

    protected function setUp(): void
    {
        $backendUrl = $this->getMockForAbstractClass(UrlInterface::class);
        $backendUrl->expects($this->once())->method('getUrl')->with("*/*/exportTablerates", ['website' => 1]);

        $objectManager = new ObjectManager($this);
        $this->_object = $objectManager->getObject(
            Export::class,
            ['backendUrl' => $backendUrl]
        );
    }

    public function testGetElementHtml()
    {
        $expected = 'some test data';

        $form = $this->getMockBuilder(Form::class)
            ->addMethods(['getParent'])
            ->disableOriginalConstructor()
            ->getMock();
        $parentObjectMock = $this->createPartialMock(Template::class, ['getLayout']);
        $layoutMock = $this->createMock(Layout::class);

        $blockMock = $this->createMock(Button::class);

        $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
        $requestMock->expects($this->once())->method('getParam')->with('website')->willReturn(1);

        $mockData = $this->getMockBuilder(\stdClass::class)->addMethods(['toHtml'])
            ->disableOriginalConstructor()
            ->getMock();
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
