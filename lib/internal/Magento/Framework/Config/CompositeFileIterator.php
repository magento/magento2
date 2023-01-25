<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Config;

use Magento\Framework\Filesystem\File\ReadFactory;

/**
 * Combine existing file iterator and new files.
 */
class CompositeFileIterator extends FileIterator
{
    /**
     * @var FileIterator
     */
    private $existingIterator;

    /**
     * @param ReadFactory $readFactory
     * @param array $paths
     * @param FileIterator $existingIterator
     */
    public function __construct(ReadFactory $readFactory, array $paths, FileIterator $existingIterator)
    {
        parent::__construct($readFactory, $paths);
        $this->existingIterator = $existingIterator;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->existingIterator->rewind();
        parent::rewind();
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        if ($this->existingIterator->valid()) {
            return $this->existingIterator->current();
        }

        return parent::current();
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        if ($this->existingIterator->valid()) {
            return $this->existingIterator->key();
        }

        return parent::key();
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        if ($this->existingIterator->valid()) {
            $this->existingIterator->next();
        } else {
            parent::next();
        }
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->existingIterator->valid() || parent::valid();
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return array_merge($this->existingIterator->toArray(), parent::toArray());
    }

    /**
     * @inheritDoc
     */
    public function count()
    {
        return $this->existingIterator->count() + parent::count();
    }
}
