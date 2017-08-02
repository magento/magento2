<?php
/**
 * Reader responsible for retrieving provided scope of configuration from storage
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config reader interface.
 *
 * @api
 * @since 2.0.0
 */
interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @return array
     * @since 2.0.0
     */
    public function read($scope = null);
}
