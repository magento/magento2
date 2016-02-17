<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Recursive iterator for PHP_CodeCoverage_Report_Node object graphs.
 *
 * @since Class available since Release 1.1.0
 */
class PHP_CodeCoverage_Report_Node_Iterator implements RecursiveIterator
{
    /**
     * @var int
     */
    protected $position;

    /**
     * @var PHP_CodeCoverage_Report_Node[]
     */
    protected $nodes;

    /**
     * Constructor.
     *
     * @param PHP_CodeCoverage_Report_Node_Directory $node
     */
    public function __construct(PHP_CodeCoverage_Report_Node_Directory $node)
    {
        $this->nodes = $node->getChildNodes();
    }

    /**
     * Rewinds the Iterator to the first element.
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Checks if there is a current element after calls to rewind() or next().
     *
     * @return bool
     */
    public function valid()
    {
        return $this->position < count($this->nodes);
    }

    /**
     * Returns the key of the current element.
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Returns the current element.
     *
     * @return PHPUnit_Framework_Test
     */
    public function current()
    {
        return $this->valid() ? $this->nodes[$this->position] : null;
    }

    /**
     * Moves forward to next element.
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Returns the sub iterator for the current element.
     *
     * @return PHP_CodeCoverage_Report_Node_Iterator
     */
    public function getChildren()
    {
        return new self(
            $this->nodes[$this->position]
        );
    }

    /**
     * Checks whether the current element has children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->nodes[$this->position] instanceof PHP_CodeCoverage_Report_Node_Directory;
    }
}
