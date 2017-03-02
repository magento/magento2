<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute;

class GridTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRowUrl()
    {
        $attribute = $this->getMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class, [], [], '', false);
        $attribute->expects($this->once())->method('getAttributeId')->will($this->returnValue(2));

        $filesystem = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);

        $urlBuilder = $this->getMock(\Magento\Framework\UrlInterface::class, [], [], '', false);
        $urlBuilder->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            $this->equalTo('catalog/*/edit'),
            $this->equalTo(['attribute_id' => 2])
        )->will(
            $this->returnValue('catalog/product_attribute/edit/id/2')
        );

        $context = $this->getMock(\Magento\Backend\Block\Template\Context::class, [], [], '', false);
        $context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $context->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $data = ['context' => $context];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid $block */
        $block = $helper->getObject(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid::class, $data);

        $this->assertEquals('catalog/product_attribute/edit/id/2', $block->getRowUrl($attribute));
    }
}
