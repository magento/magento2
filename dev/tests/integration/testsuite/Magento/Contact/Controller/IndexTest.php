<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Contact\Controller;

/**
 * Contact index controller test
 */
class IndexTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testPostAction()
    {
        $params = [
            'name' => 'customer name',
            'comment' => 'comment',
            'email' => 'user@example.com',
            'hideit' => '',
        ];
        $this->getRequest()->setPostValue($params);

        $this->dispatch('contact/index/post');
        $this->assertRedirect($this->stringContains('contact/index'));
        $this->assertSessionMessages(
            $this->contains(
                "Thanks for contacting us with your comments and questions. We'll respond to you very soon."
            ),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @dataProvider dataInvalidPostAction
     * @param $params
     * @param $expectedMessage
     */
    public function testInvalidPostAction($params, $expectedMessage)
    {
        $this->getRequest()->setPostValue($params);

        $this->dispatch('contact/index/post');
        $this->assertRedirect($this->stringContains('contact/index'));
        $this->assertSessionMessages(
            $this->contains($expectedMessage),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    public static function dataInvalidPostAction()
    {
        return [
            'missing_comment' => [
                'params' => [
                    'name' => 'customer name',
                    'comment' => '',
                    'email' => 'user@example.com',
                    'hideit' => '',
                ],
                'expectedMessage' => "Comment is missing",
            ],
            'missing_name' => [
                'params' => [
                    'name' => '',
                    'comment' => 'customer comment',
                    'email' => 'user@example.com',
                    'hideit' => '',
                ],
                'expectedMessage' => "Name is missing",
            ],
            'invalid_email' => [
                'params' => [
                    'name' => 'customer name',
                    'comment' => 'customer comment',
                    'email' => 'invalidemail',
                    'hideit' => '',
                ],
                'expectedMessage' => "Invalid email address",
            ],
        ];
    }
}
