<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
