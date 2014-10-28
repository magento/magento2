<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array('urlBuilder' => $this->urlBuilder)
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
        $expected = array('source' => $source, 'minLength' => 0, 'className' => 'category-select', 'showAll' => true);
        $this->assertEquals($expected, $this->attributeSelector->getSuggestWidgetOptions());
    }
}
