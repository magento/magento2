<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\SendFriend;

use Magento\Framework\DataObjectFactory;
use Magento\SendFriend\Model\SendFriend;
use Magento\SendFriend\Model\SendFriendFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

class SendFriendTest extends GraphQlAbstract
{

    /**
     * @var SendFriendFactory
     */
    private $sendFriendFactory;
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    protected function setUp()
    {
        $this->dataObjectFactory = Bootstrap::getObjectManager()->get(DataObjectFactory::class);
        $this->sendFriendFactory = Bootstrap::getObjectManager()->get(SendFriendFactory::class);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
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

        $response = $this->graphQlQuery($query);
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
        $this->expectExceptionMessage('The product that was requested doesn\'t exist. Verify the product and try again.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
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
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
     */
    public function testSendWithoutRecipentsName()
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
                  name: ""
                  email:"recipient1@mail.com"
               },
              {
                  name: ""
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
        $this->expectExceptionMessage('Please provide Name for all of recipients.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
     */
    public function testSendWithoutRecipentsEmail()
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
                  email:""
               },
              {
                  name: "Recipient Name 2"
                  email:""
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
        $this->expectExceptionMessage('Please provide Email for all of recipients.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
     */
    public function testSendWithoutSenderName()
    {
        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: 1	
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
        $this->expectExceptionMessage('Please provide Name of sender.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
     */
    public function testSendWithoutSenderEmail()
    {
        $query =
            <<<QUERY
mutation {
    sendEmailToFriend(
        input: {
          product_id: 1
          sender: {
            name: "Name"
            email: ""
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
        $this->expectExceptionMessage('Please provide Email of sender.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
     */
    public function testSendWithoutSenderMessage()
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
        $this->expectExceptionMessage('Please provide Message.');
        $this->graphQlQuery($query);
    }

    /**
     * @magentoApiDataFixture Magento/SendFriend/_files/product_simple.php
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
        $this->expectExceptionMessage("You can't send messages more than {$sendFriend->getMaxSendsToFriend()} times an hour.");

        for ($i = 0; $i <= $sendFriend->getMaxSendsToFriend() + 1; $i++) {
            $this->graphQlQuery($query);
        }
    }
}
