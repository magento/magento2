<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Url;

/**
 * Url Config Interface
 */
interface ConfigInterface
{
    /**
     * Get url config value by path
     *
     * @param string $path
     * @return mixed
     */
    public function getValue($path);
}
