<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Url;

/**
 * Url Config Interface
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Get url config value by path
     *
     * @param string $path
     * @return mixed
     * @since 2.0.0
     */
    public function getValue($path);
}
