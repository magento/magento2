<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Config Interface
 *
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Filename of view configuration
     */
    const CONFIG_FILE_NAME = 'etc/view.xml';

    /**
     * Render view config object for current package and theme
     *
     * @param array $params
     * @return \Magento\Framework\Config\View
     * @since 2.0.0
     */
    public function getViewConfig(array $params = []);
}
