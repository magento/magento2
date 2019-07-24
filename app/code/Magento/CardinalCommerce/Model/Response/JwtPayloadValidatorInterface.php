<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CardinalCommerce\Model\Response;

/**
 * Validates payload of CardinalCommerce response JWT.
 */
interface JwtPayloadValidatorInterface
{
    /**
     * Validates token payload.
     *
     * @param array $jwtPayload
     * @return bool
     */
    public function validate(array $jwtPayload);
}
