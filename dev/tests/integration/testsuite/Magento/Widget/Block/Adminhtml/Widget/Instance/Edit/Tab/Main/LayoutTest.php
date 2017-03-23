<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main;

/**
 * @magentoAppArea adminhtml
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main\Layout::class,
            '',
            [
                'data' => [
                    'widget_instance' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
                        \Magento\Widget\Model\Widget\Instance::class
                    ),
                ]
            ]
        );
        $this->_block->setLayout(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Framework\View\LayoutInterface::class
            )
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetLayoutsChooser()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\State::class
        )->setAreaCode(
            \Magento\Framework\App\Area::AREA_FRONTEND
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme();

        $actualHtml = $this->_block->getLayoutsChooser();
        $this->assertStringStartsWith('<select ', $actualHtml);
        $this->assertStringEndsWith('</select>', $actualHtml);
        $this->assertContains('id="layout_handle"', $actualHtml);
        $optionCount = substr_count($actualHtml, '<option ');
        $this->assertGreaterThan(1, $optionCount, 'HTML select tag must provide options to choose from.');
        $this->assertEquals($optionCount, substr_count($actualHtml, '</option>'));
    }
}
