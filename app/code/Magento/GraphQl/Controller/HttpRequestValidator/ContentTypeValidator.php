<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\UnsupportedMediaTypeException;
use Magento\Framework\Phrase;
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
     * @throws UnsupportedMediaTypeException
     */
    public function validate(HttpRequestInterface $request) : void
    {
        $headerName = 'Content-Type';
        $requiredHeaderValue = 'application/json';

        $headerValue = (string)$request->getHeader($headerName);
        if ($request->isPost()
            && strpos($headerValue, $requiredHeaderValue) === false
        ) {
            throw new UnsupportedMediaTypeException(
                new Phrase('Request content type must be application/json')
            );
        }
    }
}
