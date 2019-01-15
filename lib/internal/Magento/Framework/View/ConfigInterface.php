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
     */
    public function getViewConfig(array $params = []);
}
