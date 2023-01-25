<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Newsletter\Model\Subscriber;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/three_customers.php');

$objectManager = Bootstrap::getObjectManager();
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);

$customer1 = $customerRepository->get('customer@search.example.com');
$subscriber1 = $objectManager->create(Subscriber::class);
$subscriber1->setStoreId($customer1->getStoreId())
    ->setCustomerId($customer1->getId())
    ->setSubscriberEmail('customer@example.com')
    ->setSubscriberStatus(Subscriber::STATUS_SUBSCRIBED)
    ->save();

$customer2 = $customerRepository->get('customer2@search.example.com');
$subscriber2 = $objectManager->create(Subscriber::class);
$subscriber2->setStoreId($customer2->getStoreId())
    ->setCustomerId($customer2->getId())
    ->setSubscriberEmail('customer2@search.example.com')
    ->setSubscriberStatus(Subscriber::STATUS_SUBSCRIBED)
    ->save();

$customer3 = $customerRepository->get('customer3@search.example.com');
$subscriber3 = $objectManager->create(Subscriber::class);
$subscriber3->setStoreId($customer3->getStoreId())
    ->setCustomerId($customer3->getId())
    ->setSubscriberEmail('customer3@search.example.com')
    ->setSubscriberStatus(Subscriber::STATUS_SUBSCRIBED)
    ->save();
