<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * File resolver interface.
 *
 * @api
 * @since 2.0.0
 */
interface FileResolverInterface
{
    /**
     * Retrieve the list of configuration files with given name that relate to specified scope
     *
     * @param string $filename
     * @param string $scope
     * @return array
     * @since 2.0.0
     */
    public function get($filename, $scope);
}
