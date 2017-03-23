<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Block\Cms\Page;

/**
 * Test for \Magento\UrlRewrite\Block\Cms\Page\Grid
 * @magentoAppArea adminhtml
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test prepare grid
     */
    public function testPrepareGrid()
    {
        /** @var \Magento\UrlRewrite\Block\Cms\Page\Grid $gridBlock */
        $gridBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\UrlRewrite\Block\Cms\Page\Grid::class
        );
        $gridBlock->toHtml();

        foreach (['title', 'identifier', 'is_active'] as $key) {
            $this->assertInstanceOf(
                \Magento\Backend\Block\Widget\Grid\Column::class,
                $gridBlock->getColumn($key),
                'Column with key "' . $key . '" is invalid'
            );
        }

        $this->assertStringStartsWith('http://localhost/index.php', $gridBlock->getGridUrl(), 'Grid URL is invalid');

        $row = new \Magento\Framework\DataObject(['id' => 1]);
        $this->assertStringStartsWith(
            'http://localhost/index.php/backend/admin/index/edit/cms_page/1',
            $gridBlock->getRowUrl($row),
            'Grid row URL is invalid'
        );

        $this->assertEmpty(0, $gridBlock->getMassactionBlock()->getItems(), 'Grid should not have mass action items');
        $this->assertTrue($gridBlock->getUseAjax(), '"use_ajax" value of grid is incorrect');
    }

    /**
     * Test prepare grid when there is more than one store
     *
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     */
    public function testPrepareGridForMultipleStores()
    {
        /** @var \Magento\UrlRewrite\Block\Cms\Page\Grid $gridBlock */
        $gridBlock = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\UrlRewrite\Block\Cms\Page\Grid::class
        );
        $gridBlock->toHtml();
        $this->assertInstanceOf(
            \Magento\Backend\Block\Widget\Grid\Column::class,
            $gridBlock->getColumn('store_id'),
            'When there is more than one store column with key "store_id" should be present'
        );
    }
}
