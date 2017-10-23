<?php
/**
 * Application area front name resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Area;

/**
 * Every application request is executed in area context (@see \Magento\Framework\App\Area).
 * Area for request is defined by the first request path segment (admin for adminhtml area,
 * api for api area, none for frontend area). The request path segment that defines the area is called Area Front Name.
 *
 * For some areas Area Front Names are dynamic (can be configured by the user), so can not be referenced in code base.
 * This interface represents Front Name Resolvers that are called to retrieve front names
 * for areas with dynamic front names.
 *
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
