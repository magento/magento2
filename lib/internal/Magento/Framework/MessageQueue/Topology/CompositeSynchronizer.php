<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Topology;

class CompositeSynchronizer implements SynchronizerInterface
{
    /**
     * @param SynchronizerInterface[] $synchronizers
     * @throws \InvalidArgumentException
     */
    public function __construct(private readonly array $synchronizers = [])
    {
        foreach ($this->synchronizers as $name => $synchronizer) {
            if (!$synchronizer instanceof SynchronizerInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Synchronizer "%s" must implement %s, %s given.',
                        $name,
                        SynchronizerInterface::class,
                        get_debug_type($synchronizer)
                    )
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function synchronize(): array
    {
        $applied = [];
        foreach ($this->synchronizers as $synchronizer) {
            $applied[] = $synchronizer->synchronize();
        }

        return array_merge([], ...$applied);
    }
}
