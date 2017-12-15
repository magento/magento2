<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Ui\Component;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\Ui\Component\Container;

class AssignSourcesButton extends Container
{
    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');

        // Disable assign sources if stock is default
        $stockId = (int) $this->context->getRequestParam(StockInterface::STOCK_ID);
        $config['disabled'] = ($stockId === 1);

        $this->setData('config', $config);
    }
}
