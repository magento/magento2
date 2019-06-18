<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SendFriend;

use Magento\Catalog\Api\ProductRepositoryInterface;
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
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp()
    {
        $this->sendFriendFactory = Bootstrap::getObjectManager()->get(SendFriendFactory::class);
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testSendFriend()
    {
        $productId = (int)$this->productRepository->get('simple_product')->getId();
        $recipients = '{
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }';
        $query = $this->getQuery($productId, $recipients);

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
        $productId = 2018;
        $recipients = '{
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }';
        $query = $this->getQuery($productId, $recipients);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'The product that was requested doesn\'t exist. Verify the product and try again.'
        );
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testMaxSendEmailToFriend()
    {
        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        $productId = (int)$this->productRepository->get('simple_product')->getId();
        $recipients = '{
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
               }';

        $query = $this->getQuery($productId, $recipients);

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
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     * TODO: use magentoApiConfigFixture (to be merged https://github.com/magento/graphql-ce/pull/351)
     * @magentoApiDataFixture Magento/SendFriend/Fixtures/sendfriend_configuration.php
     */
    public function testLimitMessagesPerHour()
    {

        /** @var SendFriend $sendFriend */
        $sendFriend = $this->sendFriendFactory->create();

        $productId = (int)$this->productRepository->get('simple_product')->getId();
        $recipients = '{
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }';
        $query = $this->getQuery($productId, $recipients);

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
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product.php
     */
    public function testSendProductWithoutSenderEmail()
    {
        $productId = (int)$this->productRepository->get('simple_product')->getId();
        $recipients = '{
                  name: "Recipient Name 1"
                  email:""
               }';
        $query = $this->getQuery($productId, $recipients);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('GraphQL response contains errors: Please provide Email for all of recipients.');
        $this->graphQlMutation($query);
    }

    /**
     * @magentoApiDataFixture Magento/GraphQl/Catalog/_files/simple_product_without_visibility.php
     */
    public function testSendProductWithoutVisibility()
    {
        $productId = (int)$this->productRepository->get('simple_product_without_visibility')->getId();
        $recipients = '{
                  name: "Recipient Name 1"
                  email:"recipient1@mail.com"
               },
              {
                  name: "Recipient Name 2"
                  email:"recipient2@mail.com"
              }';
        $query = $this->getQuery($productId, $recipients);

        $response = $this->graphQlMutation($query);
        self::assertEquals('Name', $response['sendEmailToFriend']['sender']['name']);
        self::assertEquals('e@mail.com', $response['sendEmailToFriend']['sender']['email']);
        self::assertEquals('Lorem Ipsum', $response['sendEmailToFriend']['sender']['message']);
        self::assertEquals('Recipient Name 1', $response['sendEmailToFriend']['recipients'][0]['name']);
        self::assertEquals('recipient1@mail.com', $response['sendEmailToFriend']['recipients'][0]['email']);
        self::assertEquals('Recipient Name 2', $response['sendEmailToFriend']['recipients'][1]['name']);
        self::assertEquals('recipient2@mail.com', $response['sendEmailToFriend']['recipients'][1]['email']);
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

    /**
     * @param int $productId
     * @param string $recipients
     * @return string
     */
    private function getQuery(int $productId, string $recipients): string
    {
        return <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: {$productId}
          sender: {
            name: "Name"
            email: "e@mail.com"
            message: "Lorem Ipsum"
        }          
          recipients: [{$recipients}]
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
    }
}
