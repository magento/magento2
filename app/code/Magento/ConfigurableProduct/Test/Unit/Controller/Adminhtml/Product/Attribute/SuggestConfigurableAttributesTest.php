<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Attribute;

class SuggestConfigurableAttributesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\SuggestConfigurableAttributes
     */
    protected $suggestAttributes;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeListMock;

    protected function setUp(): void
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->responseMock = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->helperMock = $this->createMock(\Magento\Framework\Json\Helper\Data::class);
        $this->attributeListMock = $this->createMock(\Magento\ConfigurableProduct\Model\SuggestedAttributeList::class);
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
        )->willReturn(
            'attribute'
        );
        $this->attributeListMock->expects(
            $this->once()
        )->method(
            'getSuggestedAttributes'
        )->with(
            'attribute'
        )->willReturn(
            'some_value_for_json'
        );
        $this->helperMock->expects(
            $this->once()
        )->method(
            'jsonEncode'
        )->with(
            'some_value_for_json'
        )->willReturn(
            'body'
        );
        $this->responseMock->expects($this->once())->method('representJson')->with('body');
        $this->suggestAttributes->execute();
    }
}
