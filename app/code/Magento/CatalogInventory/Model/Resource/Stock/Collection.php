<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogInventory\Model\Resource\Stock;

use Magento\CatalogInventory\Api\Data\StockCollectionInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class Collection
 * @package Magento\CatalogInventory\Model\Resource\Stock
 */
class Collection extends AbstractSearchResult implements StockCollectionInterface
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->setDataInterfaceName('Magento\CatalogInventory\Api\Data\StockInterface');
    }
}
