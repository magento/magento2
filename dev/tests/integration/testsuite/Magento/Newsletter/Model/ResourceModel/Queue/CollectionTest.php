<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Model\ResourceModel\Queue;

use Magento\Newsletter\Model\Subscriber;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Customer filter fields Collection test class
 */
class CollectionTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     * @magentoDataFixture Magento/Newsletter/_files/newsletter_sample.php
     * @magentoDataFixture Magento/Newsletter/_files/queue.php
     */
    public function testLoadWithCustomerFilter()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $objectManager->get(CollectionFactory::class);
        $collection = $collectionFactory->create()->addCustomerFilter(1);
        $item = $collection->getFirstItem();

        /** @var Subscriber $subscriber */
        $subscriber = $objectManager->get(Subscriber::class);
        $subscriber->loadByCustomer(1, 1);

        self::assertEquals(
            $subscriber->getId(),
            $item->getSubscriberId(),
            'Wrong subscriber id loaded in collection.'
        );
    }
}
