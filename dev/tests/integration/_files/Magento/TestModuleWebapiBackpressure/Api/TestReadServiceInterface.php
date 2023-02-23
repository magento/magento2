<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleWebapiBackpressure\Api;

interface TestReadServiceInterface
{
    /**
     * @return string
     */
    public function read(): string;
}
