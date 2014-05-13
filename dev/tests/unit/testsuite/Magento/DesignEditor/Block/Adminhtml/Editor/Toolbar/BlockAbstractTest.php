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
namespace Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar;

class BlockAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * VDE toolbar buttons block
     *
     * @var \Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\AbstractBlock
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = $this->getMockForAbstractClass(
            'Magento\DesignEditor\Block\Adminhtml\Editor\Toolbar\AbstractBlock',
            array(),
            '',
            false
        );
    }

    public function testGetMode()
    {
        $this->_block->setMode(\Magento\DesignEditor\Model\State::MODE_NAVIGATION);
        $this->assertEquals(\Magento\DesignEditor\Model\State::MODE_NAVIGATION, $this->_block->getMode());
    }

    public function testSetMode()
    {
        $this->_block->setMode(\Magento\DesignEditor\Model\State::MODE_NAVIGATION);
        $this->assertAttributeEquals(\Magento\DesignEditor\Model\State::MODE_NAVIGATION, '_mode', $this->_block);
    }

    public function testIsNavigationMode()
    {
        $this->_block->setMode(\Magento\DesignEditor\Model\State::MODE_NAVIGATION);
        $this->assertTrue($this->_block->isNavigationMode());

        $this->_block->setMode('model_that_is_not_exist');
        $this->assertFalse($this->_block->isNavigationMode());
    }
}
