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
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Resource\Product\Collection;

class AssociatedProductUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test adding filtration by qty and stock availability to collection
     */
    public function testUpdate()
    {
        $inventory = array(
            'qty' => 'qty',
            'inventory_in_stock' => 'is_in_stock'
        );
        $collection = $this->getMockBuilder('Magento\Data\Collection\Db')
            ->disableOriginalConstructor()
            ->getMock();
        $stockItem = $this->getMockBuilder('Magento\CatalogInventory\Model\Resource\Stock\Item')
            ->disableOriginalConstructor()
            ->setMethods(array('addCatalogInventoryToProductCollection'))
            ->getMock();
        $stockItem->expects($this->any())
            ->method('addCatalogInventoryToProductCollection')
            ->with($collection, $inventory);

        $model = new \Magento\Catalog\Model\Resource\Product\Collection\AssociatedProductUpdater($stockItem);
        $model->update($collection);
    }
}
