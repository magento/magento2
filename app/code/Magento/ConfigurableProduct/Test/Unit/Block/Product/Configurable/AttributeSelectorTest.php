<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Product\Configurable;

use Magento\ConfigurableProduct\Block\Product\Configurable\AttributeSelector;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeSelectorTest extends TestCase
{
    /**
     * @var AttributeSelector
     */
    protected $attributeSelector;

    /**
     * @var MockObject
     */
    protected $urlBuilder;

    protected function setUp(): void
    {
        $helper = new ObjectManager($this);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);
        $this->attributeSelector = $helper->getObject(
            AttributeSelector::class,
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
        )->willReturn(
            'some_url'
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
        )->willReturn(
            $source
        );
        $expected = ['source' => $source, 'minLength' => 0, 'className' => 'category-select', 'showAll' => true];
        $this->assertEquals($expected, $this->attributeSelector->getSuggestWidgetOptions());
    }
}
