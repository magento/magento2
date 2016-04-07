<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

interface ModificationInterface
{
    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config);
}
