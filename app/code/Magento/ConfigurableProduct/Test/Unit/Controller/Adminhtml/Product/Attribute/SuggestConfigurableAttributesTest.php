<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Attribute;

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
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->responseMock = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->requestMock = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Framework\Json\Helper\Data::class, [], [], '', false);
        $this->attributeListMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\SuggestedAttributeList::class,
            [],
            [],
            '',
            false
        );
        $this->suggestAttributes = $helper->getObject(
            \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\SuggestConfigurableAttributes::class,
            [
                'response' => $this->responseMock,
                'request' => $this->requestMock,
                'jsonHelper' => $this->helperMock,
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
