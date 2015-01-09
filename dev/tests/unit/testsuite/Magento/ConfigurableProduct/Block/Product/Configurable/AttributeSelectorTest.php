<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Block\Product\Configurable;

class AttributeSelectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector
     */
    protected $attributeSelector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilder;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->urlBuilder = $this->getMock('Magento\Framework\UrlInterface');
        $this->attributeSelector = $helper->getObject(
            'Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector',
            ['urlBuilder' => $this->urlBuilder]
        );
    }

    public function testGetAttributeSetCreationUrl()
    {
        $this->urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/product_set/save'
        )->will(
            $this->returnValue('some_url')
        );
        $this->assertEquals('some_url', $this->attributeSelector->getAttributeSetCreationUrl());
    }

    public function testGetSuggestWidgetOptions()
    {
        $source = 'source_url';
        $this->urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            '*/product_attribute/suggestConfigurableAttributes'
        )->will(
            $this->returnValue($source)
        );
        $expected = ['source' => $source, 'minLength' => 0, 'className' => 'category-select', 'showAll' => true];
        $this->assertEquals($expected, $this->attributeSelector->getSuggestWidgetOptions());
    }
}
