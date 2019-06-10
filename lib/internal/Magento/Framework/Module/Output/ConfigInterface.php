<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Output;

/**
 * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
 * version. Module output can still be enabled/disabled in configuration files.
 */
interface ConfigInterface
{
    /**
     * Whether a module is enabled in the configuration or not
     *
     * @param string $moduleName Fully-qualified module name
     * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
     * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
     * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
     * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
     * issues that will be addressed in future releases.
     * @return boolean
     */
    public function isEnabled($moduleName);

    /**
     * Retrieve module enabled specific path
     *
     * @param string $path Fully-qualified config path
     * @deprecated 101.0.0 Magento does not support disabling/enabling modules output from the Admin Panel since 2.2.0
     * version. Module output can still be enabled/disabled in configuration files. However, this functionality should
     * not be used in future development. Module design should explicitly state dependencies to avoid requiring output
     * disabling. This functionality will temporarily be kept in Magento core, as there are unresolved modularity
     * issues that will be addressed in future releases.
     * @return boolean
     */
    public function isSetFlag($path);
}
