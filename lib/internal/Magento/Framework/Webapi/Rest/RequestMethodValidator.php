<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\Rest;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * Validator of supported HTTP methods.
 */
class RequestMethodValidator implements RequestValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function validate(Request $request): void
    {
        try {
            $request->getHttpMethod();
        } catch (InputException $e) {
            throw new WebapiException(
                __('The %1 HTTP method is not supported.', $request->getMethod()),
                0,
                WebapiException::HTTP_METHOD_NOT_ALLOWED
            );
        }
    }
}
