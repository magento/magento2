<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class NewsletterTemplateTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
        $this->getRequest()->setPostValue($post);
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
        // Loading by code, since ID will vary. template_code is not actually used to load anywhere else.
        $this->_model->load('some_unique_code', 'template_code');

        // Ensure that template is actually loaded so as to prevent a false positive on saving a *new* template
        // instead of existing one.
        $this->assertEquals('some_unique_code', $this->_model->getTemplateCode());

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
        $this->getRequest()->setPostValue($post);
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
        // Loading by code, since ID will vary. template_code is not actually used to load anywhere else.
        $this->_model->load('some_unique_code', 'template_code');

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
