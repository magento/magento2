<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Catalog\Product;

/**
 * Test for \Magento\UrlRewrite\Block\Catalog\Product\Grid
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test prepare grid
     */
    public function testPrepareGrid()
    {
        /** @var $gridBlock \Magento\UrlRewrite\Block\Catalog\Product\Grid */
        $gridBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\UrlRewrite\Block\Catalog\Product\Grid'
        );
        $gridBlock->toHtml();

        foreach (['entity_id', 'name', 'sku', 'status'] as $key) {
            $this->assertInstanceOf(
                'Magento\Backend\Block\Widget\Grid\Column',
                $gridBlock->getColumn($key),
                'Column with key "' . $key . '" is invalid'
            );
        }

        $this->assertStringStartsWith('http://localhost/index.php', $gridBlock->getGridUrl(), 'Grid URL is invalid');

        $row = new \Magento\Framework\DataObject(['id' => 1]);
        $this->assertStringStartsWith(
            'http://localhost/index.php/backend/admin/index/edit/product/1',
            $gridBlock->getRowUrl($row),
            'Grid row URL is invalid'
        );
        $this->assertStringEndsWith('/category', $gridBlock->getRowUrl($row), 'Grid row URL is invalid');

        $this->assertEmpty(0, $gridBlock->getMassactionBlock()->getItems(), 'Grid should not have mass action items');
        $this->assertTrue($gridBlock->getUseAjax(), '"use_ajax" value of grid is incorrect');
    }
}
