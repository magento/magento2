<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Indexer\Model\Processor;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class UpdateMview implements DataFixtureInterface
{
    /**
     * @param Processor $processor
     */
    public function __construct(
        private readonly Processor $processor
    ) {
    }

    /**
     * @inheritDoc
     */
    public function apply(array $data = []): ?DataObject
    {
        $this->processor->updateMview();
        return null;
    }
}
