<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        /** @var \Magento\Sales\Block\Order\Totals $block */
        $block = $layout->createBlock(\Magento\Sales\Block\Order\Totals::class, 'block');
        $block->setOrder(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order::class)
        )->setTemplate(
            'order/totals.phtml'
        );

        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\Element\Context::class
        );
        $childOne = $this->getMock(\Magento\Framework\View\Element\Text::class, ['initTotals'], [$context]);
        $childOne->expects($this->once())->method('initTotals');
        $layout->addBlock($childOne, 'child1', 'block');

        $childTwo = $this->getMock(\Magento\Framework\View\Element\Text::class, ['initTotals'], [$context]);
        $childTwo->expects($this->once())->method('initTotals');
        $layout->addBlock($childTwo, 'child2', 'block');

        $childThree = $this->getMock(\Magento\Framework\View\Element\Text::class, ['initTotals'], [$context]);
        $childThree->expects($this->once())->method('initTotals');
        $layout->addBlock($childThree, 'child3', 'block');

        $block->toHtml();
    }
}
