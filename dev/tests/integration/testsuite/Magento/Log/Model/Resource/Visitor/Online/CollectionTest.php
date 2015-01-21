<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Log\Model\Resource\Visitor\Online;

use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Log/_files/visitor_online.php
     */
    public function testAddCustomerData()
    {
        /** @var \Magento\Log\Model\Resource\Visitor\Online\Collection $visitorOnlineCollection */
        $visitorOnlineCollection = Bootstrap::getObjectManager()
            ->create('Magento\Log\Model\Resource\Visitor\Online\Collection');

        $visitorOnlineCollection->addCustomerData();

        $this->assertEquals(1, $visitorOnlineCollection->count(), "Invalid collection items quantity.");
        /** @var \Magento\Log\Model\Visitor\Online $collectionItem */
        $collectionItem = $visitorOnlineCollection->getFirstItem();

        /** @var \Magento\Log\Model\Visitor\Online $visitorOnline */
        $visitorOnline = Bootstrap::getObjectManager()
            ->create('Magento\Log\Model\Visitor\Online')
            ->load(1);

        $visitorOnline->addData([
            'customer_email' => 'customer@example.com',
            'customer_firstname' => 'John',
            'customer_lastname' => 'Smith',
        ]);

        foreach ($visitorOnline->getData() as $field => $expectedValue) {
            $this->assertEquals(
                $expectedValue,
                $collectionItem->getData($field),
                "'{$field}' field value is invalid."
            );
        }
    }
}
