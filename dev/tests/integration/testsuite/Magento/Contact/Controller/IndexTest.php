<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Contact\Controller;

use Magento\Framework\App\Request\Http as HttpRequest;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Contact index controller test
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * Test contacting.
     */
    public function testPostAction()
    {
        $params = [
            'name' => 'customer name',
            'comment' => 'comment',
            'email' => 'user@example.com',
            'hideit' => '',
        ];
        $this->getRequest()->setPostValue($params)->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('contact/index/post');
        $this->assertRedirect($this->stringContains('contact/index'));
        $this->assertSessionMessages(
            $this->containsEqual(
                "Thanks for contacting us with your comments and questions. We&#039;ll respond to you very soon."
            ),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Test validation.
     *
     * @param array $params For Request.
     * @param string $expectedMessage Expected response.
     */
    #[DataProvider('dataInvalidPostAction')]
    public function testInvalidPostAction($params, $expectedMessage)
    {
        $this->getRequest()->setPostValue($params)->setMethod(HttpRequest::METHOD_POST);

        $this->dispatch('contact/index/post');
        $this->assertRedirect($this->stringContains('contact/index'));
        $this->assertSessionMessages(
            $this->containsEqual($expectedMessage),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @return array
     */
    public static function dataInvalidPostAction()
    {
        return [
            'missing_comment' => [
                [  // $params
                    'name' => 'customer name',
                    'comment' => '',
                    'email' => 'user@example.com',
                    'hideit' => '',
                ],
                "Enter the comment and try again.",  // $expectedMessage
            ],
            'missing_name' => [
                [  // $params
                    'name' => '',
                    'comment' => 'customer comment',
                    'email' => 'user@example.com',
                    'hideit' => '',
                ],
                "Enter the Name and try again.",  // $expectedMessage
            ],
            'invalid_email' => [
                [  // $params
                    'name' => 'customer name',
                    'comment' => 'customer comment',
                    'email' => 'invalidemail',
                    'hideit' => '',
                ],
                "The email address is invalid. Verify the email address and try again.",  // $expectedMessage
            ],
        ];
    }
}
