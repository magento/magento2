<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class NewsletterQueueTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
        $this->getRequest()->setPostValue($postForQueue);

        // Loading by code, since ID will vary. template_code is not actually used to load anywhere else.
        $this->_model->load('some_unique_code', 'template_code');

        // Ensure that template is actually loaded so as to prevent a false positive on saving a *new* template
        // instead of existing one.
        $this->assertEquals('some_unique_code', $this->_model->getTemplateCode());

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
            $this->equalTo(['You saved the newsletter queue.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
