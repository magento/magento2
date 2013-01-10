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
 * @package     Mage_Adminhtml
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Newsletter_QueueControllerTest extends Mage_Backend_Utility_Controller
{
    /**
     * @var Mage_Newsletter_Model_Template
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();
        $this->_model = Mage::getModel('Mage_Newsletter_Model_Template');
    }
    public function tearDown()
    {
        /**
         * Unset messages
         */
        Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(true);
        unset($this->_model);
    }

    /**
     * @magentoDataFixture Mage/Adminhtml/controllers/_files/newsletter_sample.php
     * @magentoAppIsolation disabled
     */
    public function testSaveActionQueueTemplateAndVerifySuccessMessage()
    {
        $postForQueue = array('sender_email'=>'johndoe_gieee@unknown-domain.com',
                              'sender_name'=>'john doe',
                              'subject'=>'test subject',
                              'text'=>'newsletter text');
        $this->getRequest()->setPost($postForQueue);
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('template_id', $this->_model->getId());
        $this->dispatch('backend/admin/newsletter_queue/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertEmpty(Mage::getSingleton('Mage_Backend_Model_Session')->getMessages(false)->getErrors());

        /**
         * Check that success message is set
         */
        $successMessages = Mage::getSingleton('Mage_Backend_Model_Session')
            ->getMessages(false)->getItemsByType(Mage_Core_Model_Message::SUCCESS);
        $this->assertCount(1, $successMessages, 'Success message was not set');
        $this->assertEquals('The newsletter queue has been saved.', current($successMessages)->getCode());
    }
}
