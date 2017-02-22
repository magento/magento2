<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type;

/**
 * @magentoAppArea adminhtml
 */
class SelectTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtmlFormId()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\Select */
        $block = $layout->createBlock(
            'Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Type\Select',
            'select'
        );
        $html = $block->getPriceTypeSelectHtml();
        $this->assertContains('select_<%- data.select_id %>', $html);
        $this->assertContains('[<%- data.select_id %>]', $html);
    }
}
