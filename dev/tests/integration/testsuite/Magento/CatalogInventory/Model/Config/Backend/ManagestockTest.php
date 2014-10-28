<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        /** @var \Magento\CatalogInventory\Model\Stock\Status */
        $stockStatus = $this->getMock(
            '\Magento\CatalogInventory\Model\Stock\Status',
            ['rebuild'],
            [],
            '',
            false
        );

        $stockStatus->expects($this->exactly($callCount))
            ->method('rebuild')
            ->will($this->returnValue($stockStatus));

        $manageStock = new Managestock(
            Bootstrap::getObjectManager()->get('\Magento\Framework\Model\Context'),
            Bootstrap::getObjectManager()->get('\Magento\Framework\Registry'),
            Bootstrap::getObjectManager()->get('\Magento\Framework\App\Config\ScopeConfigInterface'),
            $stockStatus,
            Bootstrap::getObjectManager()->get('Magento\CatalogInventory\Model\Indexer\Stock\Processor'),
            Bootstrap::getObjectManager()->get('Magento\Core\Model\Resource\Config')
        );

        $manageStock->setPath('cataloginventory/item_options/manage_stock')
            ->setScope('default')
            ->setScopeId(0);

        $manageStock->setValue($newStockValue);

        // assert
        $manageStock->save();
    }
}
