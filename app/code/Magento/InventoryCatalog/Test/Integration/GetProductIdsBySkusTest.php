<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Test\Integration;

use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetProductIdsBySkusTest extends TestCase
{
    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    protected function setUp()
    {
        parent::setUp();

        $this->getProductIdsBySkus = Bootstrap::getObjectManager()->get(GetProductIdsBySkusInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     */
    public function testExecute()
    {
        $idBySku = ['search_product_1' => 101, 'search_product_2' => 102, 'search_product_3' => 103];

        self::assertEquals($idBySku, $this->getProductIdsBySkus->execute(array_keys($idBySku)));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Following products with requested skus were not found: not_existed_1, not_existed_2
     */
    public function testExecuteWithNotExistedSkus()
    {
        $skus = ['not_existed_1', 'not_existed_2', 'search_product_2'];

        $this->getProductIdsBySkus->execute($skus);
    }
}
