<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\ConfigurableProduct\Controller\Adminhtml\Product\Attribute\SuggestConfigurableAttributes;
use Magento\ConfigurableProduct\Model\SuggestedAttributeList;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SuggestConfigurableAttributesTest extends TestCase
{
    /**
     * @var SuggestConfigurableAttributes
     */
    protected $suggestAttributes;

    /**
     * @var MockObject
     */
    protected $responseMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $attributeListMock;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->responseMock = $this->createMock(Http::class);
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->attributeListMock = $this->createMock(SuggestedAttributeList::class);
        $this->suggestAttributes = $helper->getObject(
            SuggestConfigurableAttributes::class,
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
