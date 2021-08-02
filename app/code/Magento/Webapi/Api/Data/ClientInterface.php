<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Api\Data;

/**
 * Interface ClientInterface
 */
interface ClientInterface
{
    /**
     * Executes remote call
     * Unified interface for calling custom remote methods.
     *
     * @param  string $method
     * @param  array $params
     *
     * @return mixed
     */
    public function call(string $method, array $params = []);
}
