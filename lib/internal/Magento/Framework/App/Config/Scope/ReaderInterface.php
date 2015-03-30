<?php
/**
 * Scope Reader
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Config\Scope;

/**
 * @api
 */
interface ReaderInterface
{
    /**
     * Read configuration scope
     *
     * @param string|null $scope
     * @throws \Exception May throw an exception if the given scope is invalid
     * @return array
     */
    public function read($scope = null);
}
