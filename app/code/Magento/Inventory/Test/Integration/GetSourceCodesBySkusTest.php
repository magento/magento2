<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Test\Integration;

use Magento\InventoryApi\Model\GetSourceCodesBySkusInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetSourceCodesBySkusTest extends TestCase
{
    /**
     * @var GetSourceCodesBySkusInterface
     */
    private $getSourceCodesBySkus;

    protected function setUp()
    {
        parent::setUp();

        $this->getSourceCodesBySkus = Bootstrap::getObjectManager()->get(GetSourceCodesBySkusInterface::class);
    }

    /**
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/products.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/source_items.php
     */
    public function testExecute()
    {
        $sourceCodes = $this->getSourceCodesBySkus->execute(['SKU-1']);

        self::assertContains('eu-1', $sourceCodes);
        self::assertContains('eu-2', $sourceCodes);
        self::assertContains('eu-3', $sourceCodes);
        self::assertContains('eu-disabled', $sourceCodes);
        self::assertNotContains('us-1', $sourceCodes);

        $sourceCodes = $this->getSourceCodesBySkus->execute(['SKU-1', 'SKU-2', 'SKU-3']);
        self::assertContains('us-1', $sourceCodes);
    }
}
