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

        foreach (array('entity_id', 'name', 'sku', 'status') as $key) {
            $this->assertInstanceOf(
                'Magento\Backend\Block\Widget\Grid\Column',
                $gridBlock->getColumn($key),
                'Column with key "' . $key . '" is invalid'
            );
        }

        $this->assertStringStartsWith('http://localhost/index.php', $gridBlock->getGridUrl(), 'Grid URL is invalid');

        $row = new \Magento\Framework\Object(array('id' => 1));
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
