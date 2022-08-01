<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml;

/**
 * @magentoAppArea adminhtml
 */
class NewsletterTemplateTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var string
     */
    private $formKey;

    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $this->formKey = $formKey->getFormKey();
        $post = [
            'code' => 'test data',
            'subject' => 'test data2',
            'sender_email' => 'sender@email.com',
            'sender_name' => 'Test Sender Name',
            'text' => 'Template Content',
            'form_key' => $this->formKey,
        ];
        $this->getRequest()->setPostValue($post)->setMethod(\Zend\Http\Request::METHOD_POST);
        $this->model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Newsletter\Model\Template::class
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        /**
         * Unset messages
         */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)
            ->destroy();
        $this->model = null;
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testSaveActionCreateNewTemplateAndVerifySuccessMessage()
    {
        $this->getRequest()->setParam('id', $this->model->getId());

        $this->dispatch('backend/newsletter/template/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->model->load($this->getRequest()->getPostValue('code'), 'template_code');

        $this->assertEquals(0, $this->model->getIsLegacy());

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
        $this->model->load('some_unique_code', 'template_code');

        // Ensure that template is actually loaded so as to prevent a false positive on saving a *new* template
        // instead of existing one.
        $this->assertEquals('some_unique_code', $this->model->getTemplateCode());

        $this->getRequest()->setParam('id', $this->model->getId());

        $this->dispatch('backend/newsletter/template/save');

        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        $this->model->load($this->getRequest()->getPostValue('code'), 'template_code');

        $this->assertEquals(0, $this->model->getIsLegacy());

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
    public function testDeleteActionTemplateAndVerifySuccessMessage()
    {
        // Loading by code, since ID will vary. template_code is not actually used to load anywhere else.
        $this->model->load('some_unique_code', 'template_code');

        $this->getRequest()->setParam('id', $this->model->getId());
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

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_sample.php
     */
    public function testSaveActionTemplateWithGetAndVerifyRedirect()
    {
        // Loading by code, since ID will vary. template_code is not actually used to load anywhere else.
        $this->model->load('some_unique_code', 'template_code');

        $this->getRequest()->setMethod(\Zend\Http\Request::METHOD_GET)->setParam('id', $this->model->getId());
        $this->dispatch('backend/newsletter/template/save');

        $this->assertEquals(404, $this->getResponse()->getStatusCode());
    }
}
