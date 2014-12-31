<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Model\Config\Backend;

use Magento\TestFramework\Helper\Bootstrap as Bootstrap;

/**
 * Class ManagestockTest
 */
class ManagestockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testSaveAndRebuildIndex
     * @return array
     */
    public function saveAndRebuildIndexDataProvider()
    {
        return [
            [1, 1],
            [0, 0],
        ];
    }

    /**
     * Test rebuild stock indexer on stock status config save
     *
     * @dataProvider saveAndRebuildIndexDataProvider
     *
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture default/cataloginventory/item_options/manage_stock 0
     *
     * @param int $newStockValue new value for stock status
     * @param int $callCount count matcher
     */
    public function testSaveAndRebuildIndex($newStockValue, $callCount)
    {
        /** @var \Magento\CatalogInventory\Model\StockIndex */
        $stockManagement = $this->getMock(
            '\Magento\CatalogInventory\Model\StockIndex',
            ['rebuild'],
            [],
            '',
            false
        );

        $stockManagement->expects($this->exactly($callCount))
            ->method('rebuild');

        $manageStock = new Managestock(
            Bootstrap::getObjectManager()->get('Magento\Framework\Model\Context'),
            Bootstrap::getObjectManager()->get('Magento\Framework\Registry'),
            Bootstrap::getObjectManager()->get('Magento\Framework\App\Config\ScopeConfigInterface'),
            $stockManagement,
            Bootstrap::getObjectManager()->get('Magento\CatalogInventory\Model\Indexer\Stock\Processor'),
            Bootstrap::getObjectManager()->get('Magento\Core\Model\Resource\Config')
        );

        $manageStock->setPath('cataloginventory/item_options/manage_stock');
        $manageStock->setScope('default');
        $manageStock->setScopeId(0);
        $manageStock->setValue($newStockValue);

        // assert
        $manageStock->save();
    }
}
