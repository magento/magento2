<?php
/************************************************************************
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
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

/**
 * Validate the token if it is present in headers
 */
class AuthorizationRequestValidator implements HttpRequestValidatorInterface
{
    /**
     * @var UserTokenReaderInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly UserTokenReaderInterface $userTokenReader;

    /**
     * @var UserTokenValidatorInterface
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly UserTokenValidatorInterface $userTokenValidator;

    /**
     * @param UserTokenReaderInterface $tokenReader
     * @param UserTokenValidatorInterface $tokenValidator
     */
    public function __construct(
        UserTokenReaderInterface $tokenReader,
        UserTokenValidatorInterface $tokenValidator
    ) {
        $this->userTokenReader = $tokenReader;
        $this->userTokenValidator = $tokenValidator;
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
        $authorizationHeaderValue = $request->getHeader('Authorization');
        if (!$authorizationHeaderValue) {
            return;
        }

        $headerPieces = explode(' ', $authorizationHeaderValue);
        if (count($headerPieces) !== 2) {
            return;
        }

        $tokenType = strtolower(reset($headerPieces));
        if ($tokenType !== 'bearer') {
            return;
        }

        $bearerToken = end($headerPieces);
        try {
            $token = $this->userTokenReader->read($bearerToken);
        } catch (UserTokenException $exception) {
            throw new GraphQlAuthenticationException(__($exception->getMessage()));
        }

        try {
            $this->userTokenValidator->validate($token);
        } catch (AuthorizationException $exception) {
            throw new GraphQlAuthenticationException(__($exception->getMessage()));
        }
    }
}
