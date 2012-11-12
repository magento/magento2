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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Adminhtml_Block_Newsletter_Queue_Edit_Form
 */
class Mage_Adminhtml_Block_Newsletter_Queue_Edit_FormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        Mage::getConfig()->setCurrentAreaCode(Mage::helper('Mage_Backend_Helper_Data')->getAreaCode());
        $block = Mage::getObjectManager()->create('Mage_Adminhtml_Block_Newsletter_Queue_Edit_Form');
        $prepareFormMethod = new ReflectionMethod(
            'Mage_Adminhtml_Block_Newsletter_Queue_Edit_Form', '_prepareForm');
        $prepareFormMethod->setAccessible(true);

        $queue = Mage::getSingleton('Mage_Newsletter_Model_Queue');
        $statuses = array(Mage_Newsletter_Model_Queue::STATUS_NEVER, Mage_Newsletter_Model_Queue::STATUS_PAUSE);
        foreach ($statuses as $status) {
            $queue->setQueueStatus($status);
            $prepareFormMethod->invoke($block);
            $element = $block->getForm()->getElement('date');
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getTimeFormat());
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
