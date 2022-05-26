<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml;

use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 */
class NewsletterQueueTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @var \Magento\Newsletter\Model\Template
     */
    protected $_model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Backend\Model\Session::class
        )->getMessages(
            true
        );
        $this->_model = null;
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_sample.php
     * @dataProvider postValuesForRequest
     */
    public function testSaveActionQueueTemplateAndVerifySuccessMessage(array $postForQueue)
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
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

    /**
     * The data provider for possible request values of the start_at.
     *
     * @return array
     */
    public function postValuesForRequest(): array
    {
        return [
            'start_at_value_is_integer_zero' => [
                [
                    'sender_email' => 'johndoe_gieee@unknown-domain.com',
                    'sender_name' => 'john doe',
                    'subject' => 'test subject',
                    'text' => 'newsletter text',
                    'start_at' => 0
                ]
            ],
            'start_at_value_is_string_zero' => [
                [
                    'sender_email' => 'johndoe_gieee@unknown-domain.com',
                    'sender_name' => 'john doe',
                    'subject' => 'test subject',
                    'text' => 'newsletter text',
                    'start_at' => '0'
                ]
            ],
            'start_at_value_is_empty_string' => [
                [
                    'sender_email' => 'johndoe_gieee@unknown-domain.com',
                    'sender_name' => 'john doe',
                    'subject' => 'test subject',
                    'text' => 'newsletter text',
                    'start_at' => ''
                ]
            ],
            'start_at_value_not_provided' => [
                [
                    'sender_email' => 'johndoe_gieee@unknown-domain.com',
                    'sender_name' => 'john doe',
                    'subject' => 'test subject',
                    'text' => 'newsletter text'
                ]
            ],
            'start_at_value_is_date_time_string' => [
                [
                    'sender_email' => 'johndoe_gieee@unknown-domain.com',
                    'sender_name' => 'john doe',
                    'subject' => 'test subject',
                    'text' => 'newsletter text',
                    'start_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];
    }
}
