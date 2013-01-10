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
 * @category    Mage
 * @package     Mage_Theme
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_GeneralTest extends PHPUnit_Framework_TestCase
{
    /** @var Mage_Core_Model_Layout */
    protected $_layout;

    /** @var Mage_Core_Model_Theme */
    protected $_theme;

    /** @var Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General */
    protected $_block;

    protected function setUp()
    {
        $this->_layout = Mage::getModel('Mage_Core_Model_Layout');
        $this->_theme = Mage::getModel('Mage_Core_Model_Theme');
        $this->_block = $this->_layout->createBlock('Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_Tab_General');
    }

    protected function tearDown()
    {
        $this->_theme = null;
        $this->_layout = null;
        $this->_block = null;
    }

    public function testToHtmlPreviewImageNote()
    {
        Mage::register('current_theme', $this->_theme);
        $this->_block->setArea('adminhtml');

        $this->_block->toHtml();

        $noticeText = $this->_block->getForm()->getElement('preview_image')->getNote();
        $this->assertNotEmpty($noticeText);
    }
}
