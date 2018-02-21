<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar;

/**
 * @magentoAppArea adminhtml
 */
class AddTest extends \PHPUnit_Framework_TestCase
{
    public function testToHtmlFormId()
    {
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );

        $block = $layout->addBlock('Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Toolbar\Add', 'block');
        $block->setArea('adminhtml')->unsetChild('setForm');

        $childBlock = $layout->addBlock('Magento\Framework\View\Element\Template', 'setForm', 'block');
        $form = new \Magento\Framework\DataObject();
        $childBlock->setForm($form);

        $expectedId = '12121212';
        $this->assertNotContains($expectedId, $block->toHtml());
        $form->setId($expectedId);
        $this->assertContains($expectedId, $block->toHtml());
    }
}
