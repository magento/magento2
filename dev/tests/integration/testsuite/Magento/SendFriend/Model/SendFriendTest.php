<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SendFriend\Model;

use Laminas\Stdlib\Parameters;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\SendFriend\Helper\Data as SendFriendHelper;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class checks send friend model behavior
 *
 * @see \Magento\SendFriend\Model\SendFriend
 */
class SendFriendTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var SendFriend */
    private $sendFriend;

    /** @var CookieManagerInterface */
    private $cookieManager;

    /** @var RequestInterface */
    private $request;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->sendFriend = $this->objectManager->get(SendFriendFactory::class)->create();
        $this->cookieManager = $this->objectManager->get(CookieManagerInterface::class);
        $this->request = $this->objectManager->get(RequestInterface::class);
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/max_recipients 1
     *
     * @dataProvider validateDataProvider
     *
     * @param array $sender
     * @param array $recipients
     * @param string|bool $expectedResult
     * @return void
     */
    public function testValidate(array $sender, array $recipients, $expectedResult): void
    {
        $this->prepareData($sender, $recipients);
        $this->checkResult($expectedResult, $this->sendFriend->validate());
    }

    /**
     * @return array
     */
    public function validateDataProvider(): array
    {
        return [
            'valid_data' => [
                'sender' => [
                    'name' => 'Sender Name',
                    'email' => 'm1111ytest@mail.com',
                    'message' => 'test message',
                ],
                'recipients' => [
                    'name' => [
                        'recipient_name',
                    ],
                    'email' => [
                        'recipient_email@example.com',
                    ],
                ],
                'expected_result' => true,
            ],
            'empty_message' => [
                'sender' => [
                    'name' => 'Sender Name',
                    'email' => 'm1111ytest@mail.com',
                    'message' => '',
                ],
                'recipients' => [
                    'name' => [
                        'recipient name',
                    ],
                    'email' => [
                        'recipient_email@example.com',
                    ],
                ],
                'expected_result' => 'Please enter a message.',
            ],
            'empty_sender_name' => [
                'sender' => [
                    'name' => '',
                    'email' => 'customer_email@example.com',
                    'message' => 'test message',
                ],
                'recipients' => [
                    'name' => [
                        'recipient name',
                    ],
                    'email' => [
                        'recipient_email@example.com',
                    ],
                ],
                'expected_result' => 'Please enter a sender name.',
            ],
            'empty_recipients' => [
                'sender' => [
                    'name' => 'Sender Name',
                    'email' => 'm1111ytest@mail.com',
                    'message' => 'test message',
                ],
                'recipients' => [
                    'name' => [],
                    'email' => [],
                ],
                'expected_result' => 'Please specify at least one recipient.',
            ],
            'wrong_recipient_email' => [
                'sender' => [
                    'name' => 'Sender Name',
                    'email' => 'm1111ytest@mail.com',
                    'message' => 'test message',
                ],
                'recipients' => [
                    'name' => [
                        'recipient name',
                    ],
                    'email' => [
                        '123123',
                    ],
                ],
                'expected_result' => 'Please enter a correct recipient email address.',
            ],
            'to_much_recipients' => [
                'sender' => [
                    'name' => 'Sender Name',
                    'email' => 'm1111ytest@mail.com',
                    'message' => 'test message',
                ],
                'recipients' => [
                    'name' => [
                        'recipient name',
                        'second name',
                    ],
                    'email' => [
                        'recipient_email@example.com',
                        'recipient2_email@example.com',
                    ],
                ],
                'expected_result' => 'No more than 1 emails can be sent at a time.',
            ],
        ];
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/check_by 0
     * @magentoConfigFixture current_store sendfriend/email/max_per_hour 1
     *
     * @return void
     */
    public function testisExceedLimitByCookies(): void
    {
        $this->cookieManager->setPublicCookie(SendFriendHelper::COOKIE_NAME, (string)time());
        $this->assertTrue($this->sendFriend->isExceedLimit());
    }

    /**
     * @magentoConfigFixture current_store sendfriend/email/check_by 1
     * @magentoConfigFixture current_store sendfriend/email/max_per_hour 1
     *
     * @magentoDataFixture Magento/SendFriend/_files/sendfriend_log_record_half_hour_before.php
     *
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testisExceedLimitByIp(): void
    {
        $this->markTestSkipped('Blocked by MC-31968');
        $parameters = $this->objectManager->create(Parameters::class);
        $parameters->set('REMOTE_ADDR', '127.0.0.1');
        $this->request->setServer($parameters);
        $this->assertTrue($this->sendFriend->isExceedLimit());
    }

    /**
     * Check result
     *
     * @param array|bool $expectedResult
     * @param array|bool $result
     * @return void
     */
    private function checkResult($expectedResult, $result): void
    {
        if ($expectedResult === true) {
            $this->assertTrue($result);
        } else {
            $this->assertEquals($expectedResult, (string)reset($result) ?? '');
        }
    }

    /**
     * Prepare sender and recipient data
     *
     * @param array $sender
     * @param array $recipients
     * @return void
     */
    private function prepareData(array $sender, array $recipients): void
    {
        $this->sendFriend->setSender($sender);
        $this->sendFriend->setRecipients($recipients);
    }
}
