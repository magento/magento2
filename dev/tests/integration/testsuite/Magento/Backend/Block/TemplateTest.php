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
