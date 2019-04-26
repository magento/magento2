<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SendFriend;

use Magento\SendFriend\Model\SendFriend;
use Magento\SendFriend\Model\SendFriendFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Tests for send email to friend
 */
class SendFriendTest extends GraphQlAbstract
{

    /**
     * @var SendFriendFactory
     */
    private $sendFriendFactory;

    protected function setUp()
    {
        $this->sendFriendFactory = Bootstrap::getObjectManager()->get(SendFriendFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testSendFriend()
    {
        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: 1
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }          
          recipients: [
              {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }
          ]
        } 
    ) {
        sender {
            name
            email
            message
        }
        recipients {
            name
            email
        }
    }
}
QUERY;

        $response = $this->graphQlMutation($query);
        self::assertEquals('Name', $response['sendEmailToFriend']['sender']['name']);
        self::assertEquals('e@mail.com', $response['sendEmailToFriend']['sender']['email']);
        self::assertEquals('Lorem Ipsum', $response['sendEmailToFriend']['sender']['message']);
        self::assertEquals('Recipient Name 1', $response['sendEmailToFriend']['recipients'][0]['name']);
        self::assertEquals('recipient1@mail.com', $response['sendEmailToFriend']['recipients'][0]['email']);
        self::assertEquals('Recipient Name 2', $response['sendEmailToFriend']['recipients'][1]['name']);
        self::assertEquals('recipient2@mail.com', $response['sendEmailToFriend']['recipients'][1]['email']);
    }

    public function testSendWithoutExistProduct()
    {
        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: 2018
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }          
          recipients: [
              {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }
          ]
        } 
    ) {
        sender {
            name
            email
            message
        }
        recipients {
            name
            email
        }
    }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The product that was requested doesn\'t exist. Verify the product and try again.'
        );
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testMaxSendEmailToFriend()
    {
        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: 1
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }
          recipients: [
                {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
               {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
               {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
               {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
               {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
               {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               }
          ]
        }
    ) {
        sender {
            name
            email
            message
        }
        recipients {
            name
            email
        }
    }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No more than {$sendFriend->getMaxRecipients()} emails can be sent at a time.");
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * @dataProvider sendFriendsErrorsDataProvider
     * @param string $input
     * @param string $errorMessage
     */
    public function testErrors(string $input, string $errorMessage)
    {
        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          $input
        } 
    ) {
        sender {
            name
            email
            message
        }
        recipients {
            name
            email
        }
    }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($errorMessage);
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/product_simple.php
     * TODO: use magentoApiConfigFixture (to be merged https://github.com/magento/graphql-ce/pull/351)
     * @magentoApiDataFixture Magento/SendFriend/Fixtures/sendfriend_configuration.php
     */
    public function testLimitMessagesPerHour()
    {

        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: 1
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }
          recipients: [
                {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
               {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
               }

          ]
        }
    ) {
        sender {
            name
            email
            message
        }
        recipients {
            name
            email
        }
    }
}
QUERY;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            "You can't send messages more than {$sendFriend->getMaxSendsToFriend()} times an hour."
        );

        $maxSendToFriends = $sendFriend->getMaxSendsToFriend();
        for ($i = 0; $i <= $maxSendToFriends + 1; $i++) {
            $this->graphQlMutation($query);
        }
    }

    /**
     * @return array
     */
    public function sendFriendsErrorsDataProvider()
    {
        return [
            [
          'product_id: 1	
         sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }          
          recipients: [
              {
                  name: ""
                  email:"recipient1@mail.com"
               },
              {
                  name: ""
                  email:"recipient2@mail.com"
              }
          ]', 'Please provide Name for all of recipients.'
            ],
            [
                'product_id: 1	
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }          
          recipients: [
              {
                  name: "Recipient Name 1"
                  email:""
               },
              {
                  name: "Recipient Name 2"
                  email:""
              }
          ]', 'Please provide Email for all of recipients.'
            ],
            [
                'product_id: 1	
          sender: {
            name: ""
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }          
          recipients: [
              {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }
          ]', 'Please provide Name of sender.'
            ],
            [
                'product_id: 1	
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: ""
        }          
          recipients: [
              {
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }
          ]', 'Please provide Message.'
            ]
        ];
    }
}
