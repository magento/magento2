<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
