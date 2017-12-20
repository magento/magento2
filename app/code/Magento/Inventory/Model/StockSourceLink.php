<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Inventory\Model\ResourceModel\StockSourceLink as StockSourceLinkResourceModel;

/**
 * Doesn't have API interface because this object is need only for internal module using
 *
 * @codeCoverageIgnore
 */
class StockSourceLink extends AbstractModel
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const SOURCE_CODE = 'source_code';
    const STOCK_ID = 'stock_id';
    /**#@-*/

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(StockSourceLinkResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getSourceCode()
    {
        return $this->getData(self::SOURCE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSourceCode($sourceCode)
    {
        $this->setData(self::SOURCE_CODE, $sourceCode);
    }

    /**
     * @inheritdoc
     */
    public function getStockId()
    {
        return $this->getData(self::STOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStockId($stockId)
    {
        $this->setData(self::STOCK_ID, $stockId);
    }
}
