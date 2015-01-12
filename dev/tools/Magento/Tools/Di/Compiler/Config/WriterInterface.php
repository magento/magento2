<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tools\Di\Compiler\Config;

interface WriterInterface
{
    /**
     * Writes config in storage
     *
     * @param string $areaCode
     * @param array $config
     * @return void
     */
    public function write($areaCode, array $config);
}
