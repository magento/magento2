<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute;

class SuggestConfigurableAttributesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\SuggestConfigurableAttributes
     */
    protected $suggestAttributes;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeListMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->responseMock = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->helperMock = $this->getMock('Magento\Core\Helper\Data', [], [], '', false);
        $this->attributeListMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\SuggestedAttributeList',
            [],
            [],
            '',
            false
        );
        $this->suggestAttributes = $helper->getObject(
            'Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\SuggestConfigurableAttributes',
            [
                'response' => $this->responseMock,
                'request' => $this->requestMock,
                'coreHelper' => $this->helperMock,
                'attributeList' => $this->attributeListMock
            ]
        );
    }

    public function testIndexAction()
    {
        $this->requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'label_part'
        )->will(
            $this->returnValue('attribute')
        );
        $this->attributeListMock->expects(
            $this->once()
        )->method(
            'getSuggestedAttributes'
        )->with(
            'attribute'
        )->will(
            $this->returnValue('some_value_for_json')
        );
        $this->helperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->with(
            'some_value_for_json'
        )->will(
            $this->returnValue('body')
        );
        $this->responseMock->expects($this->once())->method('representJson')->with('body');
        $this->suggestAttributes->execute();
    }
}
