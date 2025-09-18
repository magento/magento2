<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Controller\HttpRequestValidator;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\GraphQl\Controller\HttpRequestValidatorInterface;
use Magento\Integration\Api\Exception\UserTokenException;
use Magento\Integration\Api\UserTokenReaderInterface;
use Magento\Integration\Api\UserTokenValidatorInterface;

class AuthorizationRequestValidator implements HttpRequestValidatorInterface
{
    private const AUTH = 'Authorization';
    private const BEARER = 'bearer';

    /**
     * AuthorizationRequestValidator Constructor
     *
     * @param UserTokenReaderInterface $tokenReader
     * @param UserTokenValidatorInterface $tokenValidator
     */
    public function __construct(
        private readonly UserTokenReaderInterface $tokenReader,
        private readonly UserTokenValidatorInterface $tokenValidator
    ) {
    }

    /**
     * Validate the authorization header bearer token if it is set
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlAuthenticationException
     */
    public function validate(HttpRequestInterface $request): void
    {
        $authorizationHeaderValue = $request->getHeader(self::AUTH);
        if (!$authorizationHeaderValue) {
            return;
        }

        $headerPieces = explode(' ', $authorizationHeaderValue);
        if (count($headerPieces) !== 2 || strtolower($headerPieces[0]) !== self::BEARER) {
            return;
        }

        try {
            $this->tokenValidator->validate($this->tokenReader->read($headerPieces[1]));
        } catch (UserTokenException | AuthorizationException $exception) {
            throw new GraphQlAuthenticationException(__($exception->getMessage()));
        }
    }
}
