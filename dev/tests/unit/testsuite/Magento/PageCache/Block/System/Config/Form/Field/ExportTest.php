<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block\System\Config\Form\Field;

class ExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Block\System\Config\Form\Field\Export
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new StubExport();
    }

    /**
     * Test Case for Retrieving 'Export VCL' button HTML markup
     */
    public function testGetElementHtml()
    {
        $expected = 'some test data';
        $elementMock = $this->getMock(
            '\Magento\Framework\Data\Form\Element\AbstractElement',
            [],
            [],
            '',
            false,
            false
        );

        $form = $this->getMock('Magento\Framework\Data\Form', ['getLayout'], [], '', false, false);
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false, false);

        $buttonMock = $this->getMock('Magento\Backend\Block\Widget\Button', [], [], '', false, false);
        $urlBuilderMock = $this->getMock('Magento\Backend\Model\Url', ['getUrl'], [], '', false, false);
        $urlBuilderMock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/PageCache/exportVarnishConfig',
            ['website' => 1]
        )->will(
            $this->returnValue('/PageCache/exportVarnishConfig/')
        );
        $this->_model->setUrlBuilder($urlBuilderMock);

        $requestMock = $this->getMock('Magento\Framework\App\RequestInterface', [], [], '', false, false);
        $requestMock->expects($this->once())->method('getParam')->with('website')->will($this->returnValue(1));

        $mockData = $this->getMock('Magento\Framework\Object', ['toHtml']);
        $mockData->expects($this->once())->method('toHtml')->will($this->returnValue($expected));

        $buttonMock->expects($this->once())->method('getRequest')->will($this->returnValue($requestMock));
        $buttonMock->expects($this->any())->method('setData')->will($this->returnValue($mockData));

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($buttonMock));
        $form->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));

        $this->_model->setForm($form);
        $this->assertEquals($expected, $this->_model->getElementHtml($elementMock));
    }
}
