<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Response;

/**
 * Parses content of CardinalCommerce response JWT.
 *
 * @api
 */
interface JwtParserInterface
{
    /**
     * Returns response JWT content.
     *
     * @param string $jwt
     * @return array
     */
    public function execute(string $jwt): array;
}
