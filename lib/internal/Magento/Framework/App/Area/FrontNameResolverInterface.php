<?php
/**
 * Application area front name resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param bool $checkHost if true, return front name only if it is valid for the current host
     * @return string|bool
     */
    public function getFrontName($checkHost = false);
}
