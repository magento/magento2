<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Api;

/**
 * Service to retrieve menu structure.
 *
 * All implementations of interface must return stateless structure so it can be safely cached.
 *
 * @api
 */
interface GetMenuInterface
{
    /**
     * Collect list of menu items.
     *
     * @return \Magento\Theme\Api\Data\MenuNodeInterface[]
     */
    public function execute(): array;
}
