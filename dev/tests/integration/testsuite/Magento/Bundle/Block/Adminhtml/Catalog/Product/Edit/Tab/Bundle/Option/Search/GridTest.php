<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search;

class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testToHtmlHasOnClick()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout',
            ['area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE]
        );
        $block = $layout->createBlock(
            'Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Bundle\Option\Search\Grid',
            'block'
        );
        $block->setId('temp_id');

        $html = $block->toHtml();

        $regexpTemplate = '/<button [^>]* onclick="temp_id[^"]*\\.%s/i';
        $jsFuncs = ['doFilter', 'resetFilter'];
        foreach ($jsFuncs as $func) {
            $regexp = sprintf($regexpTemplate, $func);
            $this->assertRegExp($regexp, $html);
        }
    }
}
