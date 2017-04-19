<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class FullTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full
     */
    protected $fullAction;

    protected function setUp()
    {
        $this->fullAction = Bootstrap::getObjectManager()->create(
            \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\Full::class
        );
    }

    public function testRebuildStoreIndexConfigurable()
    {
        $storeId = 1;

        $simpleProductId = $this->getIdBySku('simple_10');
        $configProductId = $this->getIdBySku('configurable');

        $expected = [
            $simpleProductId,
            $configProductId
        ];
        $storeIndexDataSimple = $this->fullAction->rebuildStoreIndex($storeId, [$simpleProductId]);
        $storeIndexDataExpected = $this->fullAction->rebuildStoreIndex($storeId, $expected);

        $this->assertEquals($storeIndexDataSimple, $storeIndexDataExpected);
    }

    /**
     * @param string $sku
     * @return int
     */
    private function getIdBySku($sku)
    {
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->get(Product::class);

        return $product->getIdBySku($sku);
    }
}
