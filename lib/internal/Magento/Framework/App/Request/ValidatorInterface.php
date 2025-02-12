<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Validate interface before giving passing it to an ActionInterface.
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * Validate request and throw the exception if it's invalid.
     *
     * @param RequestInterface $request
     * @param ActionInterface $action
     * @throws InvalidRequestException If request was invalid.
     *
     * @return void
     */
    public function validate(
        RequestInterface $request,
        ActionInterface $action
    ): void;
}
