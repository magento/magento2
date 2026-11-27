<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

/**
 * A bounded set of changelog action IDs and the highest version it covers.
 */
class ChangelogBatch extends \Magento\Framework\DataObject implements \IteratorAggregate
{
    /**
     * Return the action payload for this batch.
     *
     * @return array
     */
    public function getIds(): array
    {
        return (array)$this->getData('ids');
    }

    /**
     * Return the highest changelog version represented by this batch.
     *
     * @return int
     */
    public function getMaxVersionId(): int
    {
        return (int)$this->getData('max_version_id');
    }

    /**
     * Whether the cursor can be persisted before the whole walk completes.
     *
     * @return bool
     */
    public function canCheckpoint(): bool
    {
        return (bool)($this->getData('can_checkpoint') ?? true);
    }

    /**
     * Iterate over the action payload while retaining cursor metadata.
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->getIds());
    }
}
