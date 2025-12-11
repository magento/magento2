<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Use this interface to implement a validator for a Graphql HTTP requests
 *
 * @api
 */
interface HttpRequestValidatorInterface
{
    /**
     * Perform validation of request
     *
     * @param HttpRequestInterface $request
     * @return void
     * @throws GraphQlInputException
     */
    public function validate(HttpRequestInterface $request) : void;
}
