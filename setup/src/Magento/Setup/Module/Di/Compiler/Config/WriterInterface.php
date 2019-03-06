<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Compiler\Config;

/**
 * Interface \Magento\Setup\Module\Di\Compiler\Config\WriterInterface
 * @deprecated Moved to Framework to allow broader reuse
 * @see \Magento\Framework\App\ObjectManager\ConfigWriterInterface
 */
interface WriterInterface
{
    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write($key, array $config);
}
