<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller;

use Magento\Framework\App\HttpRequestInterface;

/**
 * Use this interface to implement a validator for a Graphql HTTP requests
 */
interface HttpRequestValidatorInterface
{
    /**
     * Perform validation of request
     *
     * @param HttpRequestInterface $request
     * @return void
     */
    public function validate(HttpRequestInterface $request) : void;
}
