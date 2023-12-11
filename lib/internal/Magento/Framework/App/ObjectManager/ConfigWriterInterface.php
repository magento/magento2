<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\ObjectManager;

/**
 * Write compiled object manager configuration to storage
 *
 * @api
 */
interface ConfigWriterInterface
{
    /**
     * Writes config in storage
     *
     * @param string $key
     * @param array $config
     * @return void
     */
    public function write(string $key, array $config);
}
