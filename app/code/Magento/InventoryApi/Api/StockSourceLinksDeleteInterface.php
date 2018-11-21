<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Service method for stock source links delete multiple
 * Performance efficient API
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface StockSourceLinksDeleteInterface
{
    /**
     * Remove StockSourceLink list list
     *
     * @param \Magento\InventoryApi\Api\Data\StockSourceLinkInterface[] $links
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function execute(array $links): void;
}
