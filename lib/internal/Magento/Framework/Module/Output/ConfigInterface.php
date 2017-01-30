<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Output;

/**
 * Interface for classes to check module configuration
 * @deprecated because there are tools to change the module state in the admin panel and magento 2 CLI
 */
interface ConfigInterface
{
    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @return boolean
     */
    public function isEnabled($moduleName);

    /**
     * Retrieve module enabled specific path
     *
     * @param string $path Fully-qualified config path
     * @return boolean
     */
    public function isSetFlag($path);
}
