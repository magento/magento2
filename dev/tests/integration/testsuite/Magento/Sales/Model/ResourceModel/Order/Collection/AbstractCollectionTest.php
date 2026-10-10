<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Collection;

use Magento\Framework\ObjectManagerInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class AbstractCollectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * An order without an ID has no related entities, even after the loaded state of the collection is reset.
     *
     * @magentoDataFixture Magento/Sales/_files/invoice.php
     */
    public function testSetOrderFilterKeepsCollectionEmptyForOrderWithoutId(): void
    {
        $order = $this->objectManager->get(OrderInterfaceFactory::class)->create();

        $collection = $this->objectManager->get(InvoiceCollectionFactory::class)
            ->create()
            ->setOrderFilter($order);

        $this->assertSame(0, $collection->getSize());
        $this->assertCount(0, $collection->getItems());

        $collection->clear();

        $this->assertSame(0, $collection->getSize());
        $this->assertCount(0, $collection->getItems());
    }
}
