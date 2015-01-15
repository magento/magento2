<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\Data\StockInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class Stock
 */
class Stock extends AbstractExtensibleModel implements StockInterface
{
    /**
     * Stock entity code
     */
    const ENTITY = 'cataloginventory_stock';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $eventPrefix = 'cataloginventory_stock';

    /**
     * Parameter name in event
     * In observe method you can use $observer->getEvent()->getStock() in this case
     *
     * @var string
     */
    protected $eventObject = 'stock';

    const BACKORDERS_NO = 0;

    const BACKORDERS_YES_NONOTIFY = 1;

    const BACKORDERS_YES_NOTIFY = 2;

    const STOCK_OUT_OF_STOCK = 0;

    const STOCK_IN_STOCK = 1;

    /**
     * Default stock id
     */
    const DEFAULT_STOCK_ID = 1;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\CatalogInventory\Model\Resource\Stock');
    }

    /**
     * Retrieve stock identifier
     *
     * @return int|null
     */
    public function getStockId()
    {
        return $this->_getData(self::STOCK_ID);
    }

    /**
     * Retrieve website identifier
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->_getData(self::WEBSITE_ID);
    }

    /**
     * Retrieve Stock Name
     *
     * @return string
     */
    public function getStockName()
    {
        return $this->_getData(self::STOCK_NAME);
    }
}
