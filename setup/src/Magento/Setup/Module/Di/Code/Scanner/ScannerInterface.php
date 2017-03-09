<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Code\Scanner;

interface ScannerInterface
{
    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files);
}
