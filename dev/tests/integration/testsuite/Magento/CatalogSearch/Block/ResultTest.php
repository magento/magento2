<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block;

class ResultTest extends \PHPUnit_Framework_TestCase
{
    public function testSetListOrders()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\LayoutInterface'
        );
        $layout->addBlock('Magento\Framework\View\Element\Text', 'head');
        // The tested block is using head block
        /** @var $block \Magento\CatalogSearch\Block\Result */
        $block = $layout->addBlock('Magento\CatalogSearch\Block\Result', 'block');
        $childBlock = $layout->addBlock('Magento\Framework\View\Element\Text', 'search_result_list', 'block');

        $this->assertSame($childBlock, $block->getListBlock());
    }
}
