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

use PDepend\Source\AST\AbstractASTClassOrInterface;
use PDepend\Source\AST\ASTArtifact;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTNamespace;

/**
 * This class implements a filter that is based on the namespace.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class PackageArtifactFilter implements ArtifactFilter
{
    /**
     * Regexp with ignorable namespace names and namespace name fragments.
     *
     * @var string
     */
    private $pattern = '';

    /**
     * Constructs a new namespace filter for the given list of namespace names.
     *
     * @param array(string) $namespaces
     */
    public function __construct(array $namespaces)
    {
        $patterns = array();
        foreach ($namespaces as $namespace) {
            $patterns[] = str_replace('\*', '\S*', preg_quote($namespace));
        }
        $this->pattern = '#^(' . join('|', $patterns) . ')$#D';
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
        $namespace = null;
        // NOTE: This looks a little bit ugly and it seems better to exclude
        //       \PDepend\Source\AST\ASTMethod and \PDepend\Source\AST\ASTProperty,
        //       but when PDepend supports more node types, this could produce errors.
        if ($node instanceof AbstractASTClassOrInterface) {
            $namespace = $node->getNamespace()->getName();
        } elseif ($node instanceof ASTFunction) {
            $namespace = $node->getNamespace()->getName();
        } elseif ($node instanceof ASTNamespace) {
            $namespace = $node->getName();
        }

        return (preg_match($this->pattern, $namespace) === 0);
    }
}
