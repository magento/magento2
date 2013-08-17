<?php
/**
 * Mage_Webhook_Block_Adminhtml_Registration_Failed
 *
 * @magentoAppArea adminhtml
 *
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
 * @package     Mage_Webhook
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Registration_FailedTest extends PHPUnit_Framework_TestCase
{
    public function testGetSessionError()
    {
        $objectManager = Mage::getObjectManager();

        /** @var Mage_Backend_Model_Session $session */
        $session = $objectManager->create('Mage_Backend_Model_Session');
        $context = $objectManager->create('Mage_Core_Block_Template_Context');
        $messageCollection = $objectManager->create('Mage_Core_Model_Message_Collection');
        $message = $objectManager->create('Mage_Core_Model_Message_Notice', array('code' => ''));
        $messageCollection->addMessage($message);
        $session->setData('messages', $messageCollection);

        $block = $objectManager->create('Mage_Webhook_Block_Adminhtml_Registration_Failed',
            array($session, $context));

        $this->assertEquals($message->toString(), $block->getSessionError());
    }
}