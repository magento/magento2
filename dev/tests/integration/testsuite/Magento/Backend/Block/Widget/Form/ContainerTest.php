<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Form;

/**
 * @magentoAppArea adminhtml
 */
class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetFormHtml()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        );
        // Create block with blocking _prepateLayout(), which is used by block to instantly add 'form' child
        /** @var $block \Magento\Backend\Block\Widget\Form\Container */
        $block = $this->getMockBuilder(\Magento\Backend\Block\Widget\Form\Container::class)
            ->setMethods(['_prepareLayout'])
            ->setConstructorArgs([$objectManager->create(\Magento\Backend\Block\Widget\Context::class)])
            ->getMock();

        $layout->addBlock($block, 'block');
        $form = $layout->addBlock(\Magento\Framework\View\Element\Text::class, 'form', 'block');

        $expectedHtml = '<b>html</b>';
        $this->assertNotEquals($expectedHtml, $block->getFormHtml());
        $form->setText($expectedHtml);
        $this->assertEquals($expectedHtml, $block->getFormHtml());
    }

    public function testPseudoConstruct()
    {
        /** @var $block \Magento\Backend\Block\Widget\Form\Container */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Backend\Block\Widget\Form\Container::class,
            '',
            [
                'data' => [
                    \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'user',
                    \Magento\Backend\Block\Widget\Form\Container::PARAM_MODE => 'edit',
                    \Magento\Backend\Block\Widget\Form\Container::PARAM_BLOCK_GROUP => 'Magento_User'
                ]
            ]
        );
        $this->assertInstanceOf(\Magento\User\Block\User\Edit\Form::class, $block->getChildBlock('form'));
    }
}
