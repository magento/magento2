<?php
/**
 * Copyright :copyright: Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryCatalog\Model\GetSkusByProductIdsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetSkusByProductIdsTest extends TestCase
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    protected function setUp()
    {
        $this->getSkusByProductIds = Bootstrap::getObjectManager()->get(GetSkusByProductIdsInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     */
    public function testExecute()
    {
        $skuById = [101 => 'search_product_1', 102 => 'search_product_2', 103 => 'search_product_3'];

        self::assertEquals($skuById, $this->getSkusByProductIds->execute(array_keys($skuById)));
    }
}
