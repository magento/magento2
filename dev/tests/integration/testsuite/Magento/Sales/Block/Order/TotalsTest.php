<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order;

/**
 * @magentoAppArea frontend
 */
class TotalsTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtmlChildrenInitialized()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var \Magento\Sales\Block\Order\Totals $block */
        $block = $layout->createBlock('Magento\Sales\Block\Order\Totals', 'block');
        $block->setOrder(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order')
        )->setTemplate(
            'order/totals.phtml'
        );

        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\Element\Context'
        );
        $childOne = $this->getMock('Magento\Framework\View\Element\Text', ['initTotals'], [$context]);
        $childOne->expects($this->once())->method('initTotals');
        $layout->addBlock($childOne, 'child1', 'block');

        $childTwo = $this->getMock('Magento\Framework\View\Element\Text', ['initTotals'], [$context]);
        $childTwo->expects($this->once())->method('initTotals');
        $layout->addBlock($childTwo, 'child2', 'block');

        $childThree = $this->getMock('Magento\Framework\View\Element\Text', ['initTotals'], [$context]);
        $childThree->expects($this->once())->method('initTotals');
        $layout->addBlock($childThree, 'child3', 'block');

        $block->toHtml();
    }
}
