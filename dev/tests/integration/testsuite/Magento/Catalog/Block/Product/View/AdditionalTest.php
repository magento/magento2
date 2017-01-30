<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View;

class AdditionalTest extends \PHPUnit_Framework_TestCase
{
    public function testGetChildHtmlList()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Catalog\Block\Product\View\Additional */
        $block = $layout->createBlock('Magento\Catalog\Block\Product\View\Additional', 'block');

        /** @var $childFirst \Magento\Framework\View\Element\Text */
        $childFirst = $layout->addBlock('Magento\Framework\View\Element\Text', 'child1', 'block');
        $htmlFirst = '<b>Any html of child1</b>';
        $childFirst->setText($htmlFirst);

        /** @var $childSecond \Magento\Framework\View\Element\Text */
        $childSecond = $layout->addBlock('Magento\Framework\View\Element\Text', 'child2', 'block');
        $htmlSecond = '<b>Any html of child2</b>';
        $childSecond->setText($htmlSecond);

        $list = $block->getChildHtmlList();

        $this->assertInternalType('array', $list);
        $this->assertCount(2, $list);
        $this->assertContains($htmlFirst, $list);
        $this->assertContains($htmlSecond, $list);
    }
}
