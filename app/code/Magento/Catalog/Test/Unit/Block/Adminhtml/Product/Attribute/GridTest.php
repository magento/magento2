<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Attribute;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Filesystem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
    public function testGetRowUrl()
    {
        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->once())->method('getAttributeId')->will($this->returnValue(2));

        $filesystem = $this->createMock(Filesystem::class);

        $urlBuilder = $this->createMock(UrlInterface::class);
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

        $context = $this->createMock(Context::class);
        $context->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilder));
        $context->expects($this->any())->method('getFilesystem')->will($this->returnValue($filesystem));

        $data = ['context' => $context];

        $helper = new ObjectManager($this);
        /** @var Grid $block */
        $block = $helper->getObject(Grid::class, $data);

        $this->assertEquals('catalog/product_attribute/edit/id/2', $block->getRowUrl($attribute));
    }
}
