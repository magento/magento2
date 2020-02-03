<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi\Rest\Request;

/**
 * Override parameter values
 *
 * Parameters in the webapi.xml can be forced. This ensures that on specific routes, a specific value is always used.
 * For instance, if there is a ".../me/..." route, the route should use only user information specific to the
 * currently logged in user. More specifically, if there was a "/customers/me/addresses" route, the service method
 * invoked could have a signature of "getAddresses($customerId)", but in the webapi.xml, the $customerId parameter
 * would be forced to be the customer id of the current authenticated user.
 *
 * The forced override parameter configuration is in the webapi.xml.
 *
 * <data>
 *   <parameter name="customer.id" force="true">%customer_id%</parameter>
 * </data>
 *
 * Classes which implement ParamOverriderInterface would return the real value for the parameter, so a
 * ParamOverriderCustomerId would return the current authenticated user's customer id. If you
 * create new ParamOverriderInterface implementations, you can register new implementations by
 * adding to the parameter list for ParamsOverrider's dependency injection configuration.
 *
 * @api
 * @since 100.0.2
 */
interface ParamOverriderInterface
{
    /**
     * Returns the overridden value to use.
     *
     * @return string|int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOverriddenValue();
}
