<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block;

/**
 * Test class for \Magento\Backend\Block\Template.
 *
 * @magentoAppArea adminhtml
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Block\Template
     */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Backend\Block\Template'
        );
    }

    /**
     * @covers \Magento\Backend\Block\Template::getFormKey
     */
    public function testGetFormKey()
    {
        $this->assertGreaterThan(15, strlen($this->_block->getFormKey()));
    }

    /**
     * @magentoAppArea adminhtml
     * @covers \Magento\Backend\Block\Template::isOutputEnabled
     * @magentoConfigFixture current_store advanced/modules_disable_output/dummy 1
     */
    public function testIsOutputEnabledTrue()
    {
        $this->_block->setData('module_name', 'dummy');
        $this->assertFalse($this->_block->isOutputEnabled('dummy'));
    }

    /**
     * @magentoAppArea adminhtml
     * @covers \Magento\Backend\Block\Template::isOutputEnabled
     * @magentoConfigFixture current_store advanced/modules_disable_output/dummy 0
     */
    public function testIsOutputEnabledFalse()
    {
        $this->_block->setData('module_name', 'dummy');
        $this->assertTrue($this->_block->isOutputEnabled('dummy'));
    }
}
