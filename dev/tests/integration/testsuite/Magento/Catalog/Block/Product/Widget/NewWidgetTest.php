<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\Widget;

/**
 * Test class for \Magento\Catalog\Block\Product\Widget\NewWidget.
 */
class NewWidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\Widget\NewWidget
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Catalog\Block\Product\Widget\NewWidget::class
        );
    }

    public function testGetCacheKeyInfo()
    {
        $requestParams = ['test' => 'data'];

        $this->_block->getRequest()->setParams($requestParams);

        $info = $this->_block->getCacheKeyInfo();

        $this->assertEquals('CATALOG_PRODUCT_NEW', $info[0]);
        $this->assertEquals(json_encode($requestParams), $info[8]);
    }
}
