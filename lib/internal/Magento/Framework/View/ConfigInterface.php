<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View;

/**
 * Config Interface
 *
 * @api
 * @since 100.0.2
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
