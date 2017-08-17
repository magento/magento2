<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Output;

/**
 * Checks whether the module is enabled in the configuration.
 *
 * @deprecated 100.2.0 Magento does not support custom disabling/enabling module output since 2.2.0 version
 */
interface ConfigInterface
{
    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @deprecated 100.2.0 Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return boolean
     */
    public function isEnabled($moduleName);

    /**
     * Retrieve module enabled specific path
     *
     * @param string $path Fully-qualified config path
     * @deprecated 100.2.0 Magento does not support custom disabling/enabling module output since 2.2.0 version
     * @return boolean
     */
    public function isSetFlag($path);
}
