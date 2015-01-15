<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget;

/**
 * @magentoAppArea adminhtml
 */
class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPseudoConstruct()
    {
        /** @var $block \Magento\Backend\Block\Widget\Container */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Widget\Container',
            '',
            [
                'data' => [
                    \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'one',
                    \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => 'two',
                ]
            ]
        );
        $this->assertStringEndsWith('one', $block->getHeaderCssClass());
        $this->assertContains('two', $block->getHeaderText());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetButtonsHtml()
    {
        $titles = [1 => 'Title 1', 'Title 2', 'Title 3'];
        $block = $this->_buildBlock($titles);
        $html = $block->getButtonsHtml('header');

        $this->assertContains('<button', $html);
        foreach ($titles as $title) {
            $this->assertContains($title, $html);
        }
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testUpdateButton()
    {
        $originalTitles = [1 => 'Title 1', 'Title 2', 'Title 3'];
        $newTitles = [1 => 'Button A', 'Button B', 'Button C'];

        $block = $this->_buildBlock($originalTitles);
        foreach ($newTitles as $id => $newTitle) {
            $block->updateButton($id, 'title', $newTitle);
        }
        $html = $block->getButtonsHtml('header');
        foreach ($newTitles as $newTitle) {
            $this->assertContains($newTitle, $html);
        }
    }

    /**
     * Composes a container with several buttons in it
     *
     * @param array $titles
     * @param string $blockName
     * @return \Magento\Backend\Block\Widget\Container
     */
    protected function _buildBlock($titles, $blockName = 'block')
    {
        /** @var $layout \Magento\Framework\View\LayoutInterface */
        $layout = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        );
        /** @var $block \Magento\Backend\Block\Widget\Container */
        $block = $layout->createBlock('Magento\Backend\Block\Widget\Container', $blockName);
        foreach ($titles as $id => $title) {
            $block->addButton($id, ['title' => $title], 0, 0, 'header');
        }
        $block->setLayout($layout);
        return $block;
    }
}
