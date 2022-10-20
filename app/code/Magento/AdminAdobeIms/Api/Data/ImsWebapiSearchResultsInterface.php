<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Api\Data;

use Magento\AdminAdobeIms\Api\Data\ImsWebapiInterface;
use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface ImsWebapiSearchResultsInterface
 *
 * @api
 */
interface ImsWebapiSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get ims token list.
     *
     * @return ImsWebapiInterface[]
     */
    public function getItems();

    /**
     * Set ims token list.
     *
     * @param ImsWebapiInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
