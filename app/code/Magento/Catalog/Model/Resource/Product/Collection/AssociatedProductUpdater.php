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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Associated product resource collection
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Resource\Product\Collection;

class AssociatedProductUpdater
    implements \Magento\Core\Model\Layout\Argument\UpdaterInterface
{
    /**
     * Stock Item instance
     *
     * @var \Magento\CatalogInventory\Model\Resource\Stock\Item
     */
    protected $_stockItem;

    /**
     * Updater constructor
     *
     * @param \Magento\CatalogInventory\Model\Resource\Stock\Item $stockItem
     */
    public function __construct(\Magento\CatalogInventory\Model\Resource\Stock\Item $stockItem)
    {
        $this->_stockItem = $stockItem;
    }

    /**
     * Add filtration by qty and stock availability
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection\AssociatedProduct $collection
     * @return mixed
     */
    public function update($collection)
    {
        $this->_stockItem->addCatalogInventoryToProductCollection(
            $collection,
            array(
                'qty' => 'qty',
                'inventory_in_stock' => 'is_in_stock'
            )
        );
        return $collection;
    }
}
