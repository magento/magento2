<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

/**
 * Interface \Magento\Setup\Module\Di\Code\Scanner\ScannerInterface
 *
 * @since 2.0.0
 */
interface ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     * @since 2.0.0
     */
    public function collectEntities(array $files);
}
