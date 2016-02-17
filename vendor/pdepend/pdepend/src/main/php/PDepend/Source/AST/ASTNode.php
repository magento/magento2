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
 * @since 0.9.6
 */

namespace PDepend\Source\AST;

/**
 * This is an abstract base implementation of the ast node interface.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.6
 */
abstract class ASTNode
{
    /**
     * Parsed child nodes of this node.
     *
     * @var \PDepend\Source\AST\ASTNode[]
     */
    protected $nodes = array();

    /**
     * The parent node of this node or <b>null</b> when this node is the root
     * of a node tree.
     *
     * @var \PDepend\Source\AST\ASTNode
     */
    protected $parent = null;

    /**
     * An optional doc comment for this node.
     *
     * @var string
     */
    protected $comment = null;

    /**
     * Metadata for this node instance, serialized in a string. This string
     * contains the start, end line, and the start, end column and the node
     * image in a colon seperated string.
     *
     * @var   string
     * @since 0.10.4
     */
    protected $metadata = '::::';

    /**
     * Constructs a new ast node instance.
     *
     * @param string $image The source image for this node.
     */
    public function __construct($image = null)
    {
        $this->metadata = str_repeat(':', $this->getMetadataSize() - 1);

        $this->setImage($image);
    }

    /**
     * Sets the image for this ast node.
     *
     * @param string $image The image for this node.
     *
     * @return void
     * @since  0.10.4
     */
    public function setImage($image)
    {
        $this->setMetadata(4, $image);
    }

    /**
     * Returns the source image of this ast node.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getMetadata(4);
    }

    /**
     * Sets the start line for this ast node.
     *
     * @param integer $startLine The node start line.
     *
     * @return void
     * @since  0.9.12
     */
    public function setStartLine($startLine)
    {
        $this->setMetadataInteger(0, $startLine);
    }

    /**
     * Returns the start line for this ast node.
     *
     * @return integer
     */
    public function getStartLine()
    {
        return $this->getMetadataInteger(0);
    }

    /**
     * Sets the start column for this ast node.
     *
     * @param integer $startColumn The node start column.
     *
     * @return void
     * @since  0.9.12
     */
    public function setStartColumn($startColumn)
    {
        $this->setMetadataInteger(2, $startColumn);
    }

    /**
     * Returns the start column for this ast node.
     *
     * @return integer
     */
    public function getStartColumn()
    {
        return $this->getMetadataInteger(2);
    }

    /**
     * Sets the node's end line.
     *
     * @param integer $endLine The node's end line.
     *
     * @return void
     * @since  0.9.12
     */
    public function setEndLine($endLine)
    {
        $this->setMetadataInteger(1, $endLine);
    }

    /**
     * Returns the end line for this ast node.
     *
     * @return integer
     */
    public function getEndLine()
    {
        return $this->getMetadataInteger(1);
    }

    /**
     * Sets the node's end column.
     *
     * @param integer $endColumn The node's end column.
     *
     * @return void
     * @since  0.9.12
     */
    public function setEndColumn($endColumn)
    {
        $this->setMetadataInteger(3, $endColumn);
    }

    /**
     * Returns the end column for this ast node.
     *
     * @return integer
     */
    public function getEndColumn()
    {
        return $this->getMetadataInteger(3);
    }

    /**
     * For better performance we have moved the single setter methods for the
     * node columns and lines into this configure method.
     *
     * @param integer $startLine   The node's start line.
     * @param integer $endLine     The node's end line.
     * @param integer $startColumn The node's start column.
     * @param integer $endColumn   The node's end column.
     *
     * @return void
     * @since  0.9.10
     */
    public function configureLinesAndColumns(
        $startLine,
        $endLine,
        $startColumn,
        $endColumn
    ) {
        $this->setMetadataInteger(0, $startLine);
        $this->setMetadataInteger(1, $endLine);
        $this->setMetadataInteger(2, $startColumn);
        $this->setMetadataInteger(3, $endColumn);
    }

    /**
     * Returns an integer value that was stored under the given index.
     *
     * @param integer $index The property instance.
     *
     * @return integer
     * @since  0.10.4
     */
    protected function getMetadataInteger($index)
    {
        return (int) $this->getMetadata($index);
    }

    /**
     * Stores an integer value under the given index in the internally used data
     * string.
     *
     * @param integer $index The property instance.
     * @param integer $value The property value.
     *
     * @return void
     * @since  0.10.4
     */
    protected function setMetadataInteger($index, $value)
    {
        $this->setMetadata($index, $value);
    }

    /**
     * Returns a boolean value that was stored under the given index.
     *
     * @param integer $index The property instance.
     *
     * @return boolean
     * @since  0.10.4
     */
    protected function getMetadataBoolean($index)
    {
        return (bool) $this->getMetadata($index);
    }

    /**
     * Stores a boolean value under the given index in the internally used data
     * string.
     *
     * @param integer $index The property instance.
     * @param boolean $value The property value.
     *
     * @return void
     * @since  0.10.4
     */
    protected function setMetadataBoolean($index, $value)
    {
        $this->setMetadata($index, $value ? 1 : 0);
    }

    /**
     * Returns the value that was stored under the given index.
     *
     * @param integer $index The property instance.
     *
     * @return mixed
     * @since  0.10.4
     */
    protected function getMetadata($index)
    {
        $metadata = explode(':', $this->metadata, $this->getMetadataSize());
        return $metadata[$index];
    }

    /**
     * Stores the given value under the given index in an internal storage
     * container.
     *
     * @param integer $index The property index.
     * @param mixed   $value The property value.
     *
     * @return void
     * @since  0.10.4
     */
    protected function setMetadata($index, $value)
    {
        $metadata         = explode(':', $this->metadata, $this->getMetadataSize());
        $metadata[$index] = $value;

        $this->metadata = join(':', $metadata);
    }

    /**
     * Returns the total number of the used property bag.
     *
     * @return integer
     * @since  0.10.4
     */
    protected function getMetadataSize()
    {
        return 5;
    }

    /**
     * Returns the node instance for the given index or throws an exception.
     *
     * @param integer $index Index of the requested node.
     *
     * @return \PDepend\Source\AST\ASTNode
     * @throws \OutOfBoundsException When no node exists at the given index.
     */
    public function getChild($index)
    {
        if (isset($this->nodes[$index])) {
            return $this->nodes[$index];
        }
        throw new \OutOfBoundsException(
            sprintf(
                'No node found at index %d in node of type: %s',
                $index,
                get_class($this)
            )
        );
    }

    /**
     * This method returns all direct children of the actual node.
     *
     * @return \PDepend\Source\AST\ASTNode[]
     */
    public function getChildren()
    {
        return $this->nodes;
    }

    /**
     * This method will search recursive for the first child node that is an
     * instance of the given <b>$targetType</b>. The returned value will be
     * <b>null</b> if no child exists for that.
     *
     * @param string $targetType Searched class or interface type.
     *
     * @return \PDepend\Source\AST\ASTNode
     */
    public function getFirstChildOfType($targetType)
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof $targetType) {
                return $node;
            }
            if (($child = $node->getFirstChildOfType($targetType)) !== null) {
                return $child;
            }
        }
        return null;
    }

    /**
     * This method will search recursive for all child nodes that are an
     * instance of the given <b>$targetType</b>. The returned value will be
     * an empty <b>array</b> if no child exists for that.
     *
     * @param string $targetType Searched class or interface type.
     * @param array  &$results   Already found node instances. This parameter
     *        is only for internal usage.
     *
     * @return \PDepend\Source\AST\ASTNode[]
     */
    public function findChildrenOfType($targetType, array &$results = array())
    {
        foreach ($this->nodes as $node) {
            if ($node instanceof $targetType) {
                $results[] = $node;
            }
            $node->findChildrenOfType($targetType, $results);
        }
        return $results;
    }

    /**
     * This method adds a new child node at the first position of the children.
     *
     * @param \PDepend\Source\AST\ASTNode $node The new child node.
     *
     * @return void
     * @since  0.10.2
     */
    public function prependChild(\PDepend\Source\AST\ASTNode $node)
    {
        array_unshift($this->nodes, $node);
        $node->setParent($this);
    }

    /**
     * This method adds a new child node to this node instance.
     *
     * @param \PDepend\Source\AST\ASTNode $node The new child node.
     *
     * @return void
     */
    public function addChild(\PDepend\Source\AST\ASTNode $node)
    {
        $this->nodes[] = $node;
        $node->setParent($this);
    }

    /**
     * Returns the parent node of this node or <b>null</b> when this node is
     * the root of a node tree.
     *
     * @return \PDepend\Source\AST\ASTNode
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Traverses up the node tree and finds all parent nodes that are instances
     * of <b>$parentType</b>.
     *
     * @param string $parentType Class/interface type you are looking for,
     *
     * @return \PDepend\Source\AST\ASTNode[]
     */
    public function getParentsOfType($parentType)
    {
        $parents = array();

        $parentNode = $this->parent;
        while (is_object($parentNode)) {
            if ($parentNode instanceof $parentType) {
                array_unshift($parents, $parentNode);
            }
            $parentNode = $parentNode->getParent();
        }
        return $parents;
    }

    /**
     * Sets the parent node of this node.
     *
     * @param \PDepend\Source\AST\ASTNode $node The parent node of this node.
     *
     * @return void
     */
    public function setParent(\PDepend\Source\AST\ASTNode $node)
    {
        $this->parent = $node;
    }

    /**
     * Returns a doc comment for this node or <b>null</b> when no comment was
     * found.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Sets the raw doc comment for this node.
     *
     * @param string $comment The doc comment block for this node.
     *
     * @return void
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * The magic sleep method will be called by PHP's runtime environment right
     * before an instance of this class gets serialized. It should return an
     * array with those property names that should be serialized for this class.
     *
     * @return array
     * @since  0.10.0
     */
    public function __sleep()
    {
        return array(
            'comment',
            'metadata',
            'nodes'
        );
    }

    /**
     * The magic wakeup method will be called by PHP's runtime environment when
     * a previously serialized object gets unserialized. This implementation of
     * the wakeup method restores the dependencies between an ast node and the
     * node's children.
     *
     * @return void
     * @since  0.10.0
     */
    public function __wakeup()
    {
        foreach ($this->nodes as $node) {
            $node->parent = $this;
        }
    }
}
