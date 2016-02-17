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

namespace PDepend\Source\AST\ASTArtifactList;

use PDepend\Source\AST\ASTArtifact;

/**
 * Static composite filter for code nodes. This class is used for an overall
 * filtering.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 *
 * @fixme Never ever use a singleton!!!
 */
final class CollectionArtifactFilter implements ArtifactFilter
{
    /**
     * Singleton instance of this filter.
     *
     * @var \PDepend\Source\AST\ASTArtifactList\CollectionArtifactFilter
     */
    private static $instance = null;

    /**
     * Singleton method for this filter class.
     *
     * @return \PDepend\Source\AST\ASTArtifactList\CollectionArtifactFilter
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new CollectionArtifactFilter();
        }
        return self::$instance;
    }

    /**
     * Constructs a new static filter.
     *
     * @access private
     */
    public function __construct()
    {
    }

    /**
     * An optional configured filter instance.
     *
     * @var \PDepend\Source\AST\ASTArtifactList\ArtifactFilter
     */
    private $filter = null;

    /**
     * Sets the used filter instance.
     *
     * @param  \PDepend\Source\AST\ASTArtifactList\ArtifactFilter $filter
     * @return void
     * @since  0.9.12
     */
    public function setFilter(ArtifactFilter $filter = null)
    {
        $this->filter = $filter;
    }

    /**
     * Returns <b>true</b> if the given node should be part of the node iterator,
     * otherwise this method will return <b>false</b>.
     *
     * @param  \PDepend\Source\AST\ASTArtifact $node
     * @return boolean
     */
    public function accept(ASTArtifact $node)
    {
        if ($this->filter === null) {
            return true;
        }
        return $this->filter->accept($node);
    }
}
