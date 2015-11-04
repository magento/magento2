<?php
/**
 * Application area front name resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Area;

/**
 * Interface FrontNameResolverInterface
 * @api
 */
interface FrontNameResolverInterface
{
    /**
     * Retrieve front name
     *
     * @param null|string $host If set, only return frontname if it is valid for the host
     * @return null|string
     */
    public function getFrontName($host = null);
}
