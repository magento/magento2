<?php
/**
 * Application area front name resolver
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
