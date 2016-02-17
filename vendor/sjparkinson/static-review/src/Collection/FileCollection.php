<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE.md
 */

namespace StaticReview\Collection;

use StaticReview\File\FileInterface;

class FileCollection extends Collection
{
    /**
     * Validates that $object is an instance of FileInterface.
     *
     * @param  FileInterface            $object
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate($object)
    {
        if ($object instanceof FileInterface) {
            return true;
        }

        $exceptionMessage = $object . ' was not an instance of FileInterface.';

        throw new \InvalidArgumentException($exceptionMessage);
    }

    /**
     * Filters the collection with the given closure, returning a new collection.
     *
     * @return FileCollection
     */
    public function select(callable $filter)
    {
        if (! $this->collection) {
            return new FileCollection();
        }

        $filtered = array_filter($this->collection, $filter);

        return new FileCollection($filtered);
    }
}
