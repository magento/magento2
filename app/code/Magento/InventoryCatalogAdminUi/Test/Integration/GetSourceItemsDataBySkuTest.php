<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Test\Integration;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogAdminUi\Model\GetSourceItemsDataBySku;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetSourceItemsDataBySkuTest extends TestCase
{
    /**
     * @var GetSourceItemsDataBySku
     */
    private $getSourceItemsDataBySku;

    protected function setUp()
    {
        parent::setUp();
        $this->getSourceItemsDataBySku = Bootstrap::getObjectManager()->get(GetSourceItemsDataBySku::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testExecute()
    {
        $sourceItems = $this->getSourceItemsDataBySku->execute('SKU-1');

        $sourceCodes = [];
        foreach ($sourceItems as $sourceItem) {
            $sourceCodes[] = $sourceItem[SourceItemInterface::SOURCE_CODE];
        }

        self::assertContains('eu-1', $sourceCodes);
        self::assertContains('eu-2', $sourceCodes);
        self::assertContains('eu-3', $sourceCodes);
        self::assertContains('eu-disabled', $sourceCodes);
    }
}
