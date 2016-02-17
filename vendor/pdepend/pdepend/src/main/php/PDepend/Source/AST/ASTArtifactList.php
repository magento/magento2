<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend\Source\AST;

use PDepend\Source\AST\ASTArtifactList\CollectionArtifactFilter;

/**
 * Iterator for code nodes.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ASTArtifactList implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * List of {@link \PDepend\Source\AST\ASTArtifact} objects in
     * this iterator.
     *
     * @var \PDepend\Source\AST\ASTArtifact[]
     */
    private $artifacts = array();

    /**
     * Total number of available nodes.
     *
     * @var integer
     */
    private $count = 0;

    /**
     * Current internal offset.
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * Constructs a new node iterator from the given {@link \PDepend\Source\AST\ASTArtifact}
     * node array.
     *
     * @param \PDepend\Source\AST\ASTArtifact[] $artifacts
     */
    public function __construct(array $artifacts)
    {
        $filter = CollectionArtifactFilter::getInstance();

        $nodeKeys = array();
        foreach ($artifacts as $artifact) {
            $id = $artifact->getId();

            if (isset($nodeKeys[$id])) {
                continue;
            }

            if ($filter->accept($artifact)) {
                $nodeKeys[$id] = $id;
                $this->artifacts[]  = $artifact;

                ++$this->count;
            }
        }
    }

    /**
     * Returns the number of {@link \PDepend\Source\AST\ASTArtifact}
     * objects in this iterator.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->artifacts);
    }

    /**
     * Returns the current node or <b>false</b>
     *
     * @return \PDepend\Source\AST\ASTArtifact
     */
    public function current()
    {
        if ($this->offset >= $this->count) {
            return false;
        }
        return $this->artifacts[$this->offset];
    }

    /**
     * Returns the name of the current {@link \PDepend\Source\AST\ASTArtifact}.
     *
     * @return string
     */
    public function key()
    {
        return $this->artifacts[$this->offset]->getName();
    }

    /**
     * Moves the internal pointer to the next {@link \PDepend\Source\AST\ASTArtifact}.
     *
     * @return void
     */
    public function next()
    {
        ++$this->offset;
    }

    /**
     * Rewinds the internal pointer.
     *
     * @return void
     */
    public function rewind()
    {
        $this->offset = 0;
    }

    /**
     * Returns <b>true</b> while there is a next {@link \PDepend\Source\AST\ASTArtifact}.
     *
     * @return boolean
     */
    public function valid()
    {
        return ($this->offset < $this->count);
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean Returns true on success or false on failure. The return
     *                 value will be casted to boolean if non-boolean was returned.
     * @since  1.0.0
     * @link   http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return isset($this->artifacts[$offset]);
    }

    /**
     * Offset to retrieve
     *
     * @param  mixed $offset
     * @return \PDepend\Source\AST\ASTArtifact Can return all value types.
     * @throws \OutOfBoundsException
     * @since  1.0.0
     * @link   http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        if (isset($this->artifacts[$offset])) {
            return $this->artifacts[$offset];
        }
        throw new \OutOfBoundsException("The offset {$offset} does not exist.");
    }

    /**
     * Offset to set
     *
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     * @throws \BadMethodCallException
     * @since  1.0.0
     * @link   http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Not supported operation.');
    }

    /**
     * Offset to unset
     *
     * @param  mixed $offset
     * @return void
     * @throws \BadMethodCallException
     * @since  1.0.0
     * @link   http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Not supported operation.');
    }
}
