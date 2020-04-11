<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $backendUrl = $this->createMock(UrlInterface::class);
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

        $form = $this->createPartialMock(Form::class, ['getParent']);
        $parentObjectMock = $this->createPartialMock(Template::class, ['getLayout']);
        $layoutMock = $this->createMock(Layout::class);

        $blockMock = $this->createMock(Button::class);

        $requestMock = $this->createMock(RequestInterface::class);
        $requestMock->expects($this->once())->method('getParam')->with('website')->will($this->returnValue(1));

        $mockData = $this->createPartialMock(\stdClass::class, ['toHtml']);
        $mockData->expects($this->once())->method('toHtml')->will($this->returnValue($expected));

        $blockMock->expects($this->once())->method('getRequest')->will($this->returnValue($requestMock));
        $blockMock->expects($this->any())->method('setData')->will($this->returnValue($mockData));

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($blockMock));
        $parentObjectMock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));
        $form->expects($this->once())->method('getParent')->will($this->returnValue($parentObjectMock));

        $this->_object->setForm($form);
        $this->assertEquals($expected, $this->_object->getElementHtml());
    }
}
