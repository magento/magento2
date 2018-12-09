<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache\Identifier;

/**
 * Page Cache Identifier Modifier Interface
 */
interface ModifierInterface
{
    /**
     * @return string
     */
    public function getData();
}