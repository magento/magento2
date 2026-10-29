<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
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
