<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Rest;

use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * Interface for validating REST requests.
 */
interface RequestValidatorInterface
{
    /**
     * Validate provided request.
     *
     * @param Request $request
     * @return void
     * @throws WebapiException
     */
    public function validate(Request $request): void;
}
