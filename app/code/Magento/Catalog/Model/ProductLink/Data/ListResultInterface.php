<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink\Data;

use Magento\Catalog\Api\Data\ProductLinkInterface;

/**
 * Result of finding a list of links.
 *
 * @api
 */
interface ListResultInterface
{
    /**
     * Found links, null if error occurred.
     *
     * @return ProductLinkInterface[]|null
     */
    public function getResult(): ?array;

    /**
     * Error that occurred during retrieval of the list.
     *
     * @return \Throwable|null
     */
    public function getError(): ?\Throwable;
}
