<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Cors\Validator;

/**
 * Decides if Requested should be applied with CORS headers
 */
interface RequestValidatorInterface
{
    /**
     * Determines whether the request is in list of allowed origins
     * @return bool
     */
    public function isOriginAllowed(): bool;
}
