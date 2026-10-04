<?php
/**
 * Reader responsible for retrieving provided scope of configuration from storage
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Config;

/**
 * Config reader interface.
 *
 * @api
 * @since 100.0.2
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
