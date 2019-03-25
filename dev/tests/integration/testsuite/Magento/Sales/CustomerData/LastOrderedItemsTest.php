<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\CustomerData;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Customer\Model\Session;

/**
 * Test for LastOrderedItems.
 *
 * @magentoAppIsolation enabled
 */
class LastOrderedItemsTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Test to check count in items collection.
     *
     * @magentoDataFixture Magento/Sales/_files/order_with_customer_and_multiple_order_items.php
     */
    public function testDefaultFormatterIsAppliedWhenBasicIntegration()
    {
        /** @var Session $customerSession */
        $customerSession = $this->objectManager->get(Session::class);
        $customerSession->loginById(1);

        /** @var LastOrderedItems $customerDataSectionSource */
        $customerDataSectionSource = $this->objectManager->get(LastOrderedItems::class);
        $data = $customerDataSectionSource->getSectionData();

        $this->assertEquals(
            LastOrderedItems::SIDEBAR_ORDER_LIMIT,
            count($data['items']),
            'Section items count should not be greater then ' . LastOrderedItems::SIDEBAR_ORDER_LIMIT
        );
    }
}
