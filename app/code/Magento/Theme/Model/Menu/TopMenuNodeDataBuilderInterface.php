<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Menu;

use Magento\Theme\Api\Data\MenuNodeInterface;

/**
 * Service to build legacy tree node data based on menu item.
 *
 * Provide implementation  of this interface if you have specific menu node type.
 *
 * @api
 */
interface TopMenuNodeDataBuilderInterface
{
    /**
     * Checks if provided menu item is supported.
     *
     * @param MenuNodeInterface $menuNode
     * @return bool
     */
    public function isAcceptable(MenuNodeInterface $menuNode): bool;

    /**
     * Extracts data from interface into associative array.
     *
     * @param MenuNodeInterface $menuNode
     * @return array
     */
    public function buildData(MenuNodeInterface $menuNode): array;
}
