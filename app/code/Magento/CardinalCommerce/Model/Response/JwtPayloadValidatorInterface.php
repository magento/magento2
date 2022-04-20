<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Response;

/**
 * Validates payload of CardinalCommerce response JWT.
 *
 * @api
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
