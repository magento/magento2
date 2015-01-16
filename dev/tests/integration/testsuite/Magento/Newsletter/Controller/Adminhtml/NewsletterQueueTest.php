<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class NewsletterQueueTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Newsletter\Model\Template'
        );
    }

    protected function tearDown()
    {
        /**
         * Unset messages
         */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Backend\Model\Session'
        )->getMessages(
            true
        );
        unset($this->_model);
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_sample.php
     * @magentoAppIsolation disabled
     */
    public function testSaveActionQueueTemplateAndVerifySuccessMessage()
    {
        $postForQueue = [
            'sender_email' => 'johndoe_gieee@unknown-domain.com',
            'sender_name' => 'john doe',
            'subject' => 'test subject',
            'text' => 'newsletter text',
        ];
        $this->getRequest()->setPost($postForQueue);
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('template_id', $this->_model->getId());
        $this->dispatch('backend/newsletter/queue/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['The newsletter queue has been saved.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
