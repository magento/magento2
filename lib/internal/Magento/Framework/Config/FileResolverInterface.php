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
 */
interface FileResolverInterface
{
    /**
     * Retrieve the list of configuration files with given name that relate to specified scope
     *
     * @param string $filename
     * @param string $scope
     * @return array
     */
    public function get($filename, $scope);
}
