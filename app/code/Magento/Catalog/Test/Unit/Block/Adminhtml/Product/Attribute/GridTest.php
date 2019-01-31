<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute;

class GridTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRowUrl()
    {
        $attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attribute->expects($this->once())->method('getAttributeId')->will($this->returnValue(2));

        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);

        $urlBuilder = $this->createMock(\Magento\Framework\UrlInterface::class);
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

        $context = $this->createMock(\Magento\Backend\Block\Template\Context::class);
        $context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $context->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $data = ['context' => $context];

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid $block */
        $block = $helper->getObject(\Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid::class, $data);

        $this->assertEquals('catalog/product_attribute/edit/id/2', $block->getRowUrl($attribute));
    }
}
