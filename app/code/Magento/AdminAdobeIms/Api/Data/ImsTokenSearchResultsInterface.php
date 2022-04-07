<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api\Data;

use Magento\AdminAdobeIms\Api\Data\ImsTokenInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ImsTokenSearchResultsInterface
 *
 * @api
 */
interface ImsTokenSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get ims token list.
     *
     * @return ImsTokenInterface[]
     */
    public function getItems();

    /**
     * Set ims token list.
     *
     * @param ImsTokenInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
