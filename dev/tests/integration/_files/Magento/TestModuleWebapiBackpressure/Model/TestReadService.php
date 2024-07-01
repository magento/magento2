<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestModuleWebapiBackpressure\Model;

use Magento\TestModuleWebapiBackpressure\Api\TestReadServiceInterface;

class TestReadService implements TestReadServiceInterface
{
    /**
     * @var int
     */
    private int $counter = 0;

    /**
     * @inheritDoc
     */
    public function read(): string
    {
        $this->counter++;

        return 'read';
    }

    public function resetCounter(): void
    {
        $this->counter = 0;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }
}
