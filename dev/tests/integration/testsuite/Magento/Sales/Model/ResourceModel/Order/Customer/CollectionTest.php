<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Customer;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->collection = $objectManager->get(Collection::class);
    }

    /**
     * Attribute presence test
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @dataProvider joinAttribute
     * @param string $attribute
     * @return void
     */
    public function testAttributePresent($attribute): void
    {
        $customers = $this->collection->getItems();
        foreach ($customers as $customer) {
            $this->assertNotEmpty($customer->getData($attribute), "Attribute '$attribute' is not present");
        }
    }

    /**
     * Attribute data provider
     *
     * @return array
     */
    public function joinAttribute():array
    {
        return [
            ['billing_postcode'],
            ['billing_city'],
            ['billing_telephone'],
            ['billing_region'],
            ['billing_country_id']
        ];
    }
}
