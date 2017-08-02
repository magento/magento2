<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block;

/**
 * Shortcut block interface
 *
 * @api
 * @since 2.0.0
 */
interface ShortcutInterface
{
    /**
     * Get shortcut alias
     *
     * @return string
     * @since 2.0.0
     */
    public function getAlias();
}
