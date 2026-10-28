<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use Throwable;

/**
 * Interface for providing response status code when invalid GraphQL request is detected.
 */
interface InvalidRequestInterface extends Throwable
{
    /**
     * HTTP status code to be returned with the response.
     *
     * @return int
     */
    public function getStatusCode(): int;
}
