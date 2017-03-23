<?php
/**
 * Reader responsible for retrieving provided scope of configuration from storage
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Config reader interface.
 *
 * @api
 */
interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null);
}
