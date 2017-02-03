<?php
/**
 * Application area front name resolver
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param bool if true,  only return frontname if it is valid for the host
     * @return string|bool
     */
    public function getFrontName($checkHost = false);
}
