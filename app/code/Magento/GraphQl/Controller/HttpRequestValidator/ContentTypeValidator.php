<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;

/**
 * Processes the "Content-Type" header entry
 */
class ContentTypeValidator implements HttpRequestValidatorInterface
{
    /**
     * Handle the mandatory application/json header
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request) : void
    {
        $headerName = 'Content-Type';
        $requiredHeaderValue = 'application/json';

        $headerValue = (string)$request->getHeader($headerName);
        if ($request->isPost()
            && strpos($headerValue, $requiredHeaderValue) === false
        ) {
            throw new GraphQlInputException(
                new \Magento\Framework\Phrase('Request content type must be application/json')
            );
        }
    }
}
