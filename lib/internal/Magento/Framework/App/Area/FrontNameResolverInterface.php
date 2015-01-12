<?php
/**
 * Application area front name resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Area;

interface FrontNameResolverInterface
{
    /**
     * Retrieve front name
     *
     * @return string
     */
    public function getFrontName();
}
