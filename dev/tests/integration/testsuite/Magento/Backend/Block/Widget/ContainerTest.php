<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array(
                'data' => array(
                    \Magento\Backend\Block\Widget\Container::PARAM_CONTROLLER => 'one',
                    \Magento\Backend\Block\Widget\Container::PARAM_HEADER_TEXT => 'two'
                )
            )
        );
        $this->assertStringEndsWith('one', $block->getHeaderCssClass());
        $this->assertContains('two', $block->getHeaderText());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetButtonsHtml()
    {
        $titles = array(1 => 'Title 1', 'Title 2', 'Title 3');
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
        $originalTitles = array(1 => 'Title 1', 'Title 2', 'Title 3');
        $newTitles = array(1 => 'Button A', 'Button B', 'Button C');

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
            $block->addButton($id, array('title' => $title), 0, 0, 'header');
        }
        $block->setLayout($layout);
        return $block;
    }
}
