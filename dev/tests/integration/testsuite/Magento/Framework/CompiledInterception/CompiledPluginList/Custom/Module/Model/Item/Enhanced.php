<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

// @codingStandardsIgnoreFile
namespace Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\Item;

class Enhanced extends \Magento\Framework\CompiledInterception\CompiledPluginList\Custom\Module\Model\Item
{
    /**
     * @return string
     */
    public function getName()
    {
        return ucfirst(parent::getName());
    }
}
