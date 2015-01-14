<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config;

interface DataInterface
{
    /**
     * @param string|null $path
     * @return string|array
     */
    public function getValue($path);
}
