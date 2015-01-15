<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class NewsletterTemplateTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $_model;

    protected function setUp()
    {
        parent::setUp();
        $post = [
            'code' => 'test data',
            'subject' => 'test data2',
            'sender_email' => 'sender@email.com',
            'sender_name' => 'Test Sender Name',
            'text' => 'Template Content',
        ];
        $this->getRequest()->setPost($post);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Newsletter\Model\Template'
        );
    }

    protected function tearDown()
    {
        /**
         * Unset messages
         */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session')->destroy();
        unset($this->_model);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveActionCreateNewTemplateAndVerifySuccessMessage()
    {
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('id', $this->_model->getId());
        $this->dispatch('backend/newsletter/template/save');
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);
        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['The newsletter template has been saved.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_sample.php
     */
    public function testSaveActionEditTemplateAndVerifySuccessMessage()
    {
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('id', $this->_model->getId());
        $this->dispatch('backend/newsletter/template/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['The newsletter template has been saved.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveActionTemplateWithInvalidDataAndVerifySuccessMessage()
    {
        $post = [
            'code' => 'test data',
            'subject' => 'test data2',
            'sender_email' => 'sender_email.com',
            'sender_name' => 'Test Sender Name',
            'text' => 'Template Content',
        ];
        $this->getRequest()->setPost($post);
        $this->dispatch('backend/newsletter/template/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages(
            $this->logicalNot($this->isEmpty()),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );

        /**
         * Check that success message is not set
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_sample.php
     */
    public function testDeleteActionTemplateAndVerifySuccessMessage()
    {
        $this->_model->loadByCode('some_unique_code');
        $this->getRequest()->setParam('id', $this->_model->getId());
        $this->dispatch('backend/newsletter/template/delete');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that success message is set
         */
        $this->assertSessionMessages(
            $this->equalTo(['The newsletter template has been deleted.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }
}
