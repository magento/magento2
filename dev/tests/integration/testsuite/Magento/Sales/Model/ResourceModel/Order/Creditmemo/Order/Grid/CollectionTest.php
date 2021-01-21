<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Creditmemo\Order\Grid;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Magento\Sales\Model\ResourceModel\Order\Creditmemo\Grid\Order\Grid
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/creditmemo_list.php
     *
     * @return void
     */
    public function testCollectionOrderCurrencyCodeExist(): void
    {
        /** @var $collection Collection */
        $collection = $this->objectManager->get(Collection::class);
        $collection->addFieldToFilter('increment_id', ['eq' => '456']);
        foreach ($collection as $item) {
            $this->assertNotNull($item->getOrderCurrencyCode());
        }
    }
}
