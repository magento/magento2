<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

/**
 * Interface \Magento\Setup\Module\Di\Compiler\Config\ModificationInterface
 *
 * @since 2.0.0
 */
interface ModificationInterface
{
    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     * @since 2.0.0
     */
    public function modify(array $config);
}
