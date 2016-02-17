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
 * @since 0.9.12
 */

namespace PDepend\Util;

use PDepend\Source\AST\AbstractASTArtifact;
use PDepend\Source\AST\AbstractASTType;
use PDepend\Source\AST\ASTCompilationUnit;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTMethod;

/**
 * This class provides methods to generate unique, but reproducable identifiers
 * for nodes generated during the parsing process.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.9.12
 */
class IdBuilder
{
    /**
     * Generates an identifier for the given file instance.
     *
     * @param  \PDepend\Source\AST\ASTCompilationUnit $compilationUnit
     * @return string
     */
    public function forFile(ASTCompilationUnit $compilationUnit)
    {
        return $this->hash($compilationUnit->getFileName());
    }

    /**
     * Generates an identifier for the given function instance.
     *
     * @param  \PDepend\Source\AST\ASTFunction $function
     * @return string
     */
    public function forFunction(ASTFunction $function)
    {
        return $this->forOffsetItem($function, 'function');
    }

    /**
     * Generates an identifier for the given class, interface or trait instance.
     *
     * @param  \PDepend\Source\AST\AbstractASTType $type
     * @return string
     */
    public function forClassOrInterface(AbstractASTType $type)
    {

        return $this->forOffsetItem(
            $type,
            ltrim(strrchr(strtolower(get_class($type)), '_'), '_')
        );
    }

    /**
     * Generates an identifier for the given source item.
     *
     * @param  \PDepend\Source\AST\AbstractASTArtifact $artifact
     * @param  string                                  $prefix   The item type identifier.
     * @return string
     */
    protected function forOffsetItem(AbstractASTArtifact $artifact, $prefix)
    {
        $fileHash = $artifact->getCompilationUnit()->getId();
        $itemHash = $this->hash($prefix . ':' . strtolower($artifact->getName()));

        $offset = $this->getOffsetInFile($fileHash, $itemHash);

        return sprintf('%s-%s-%s', $fileHash, $itemHash, $offset);
    }

    /**
     * Generates an identifier for the given method instance.
     *
     * @param  \PDepend\Source\AST\ASTMethod $method
     * @return string
     */
    public function forMethod(ASTMethod $method)
    {
        return sprintf(
            '%s-%s',
            $method->getParent()->getId(),
            $this->hash(strtolower($method->getName()))
        );
    }

    /**
     * Creates a base 36 hash for the given string.
     *
     * @param  string $string The raw input identifier/string.
     * @return string
     */
    protected function hash($string)
    {
        return substr(base_convert(md5($string), 16, 36), 0, 11);
    }

    /**
     * Returns the node offset/occurence of the given <b>$string</b> within a
     * file.
     *
     * @param  string $file   The file identifier.
     * @param  string $string The node identifier.
     * @return string
     */
    protected function getOffsetInFile($file, $string)
    {
        if (isset($this->offsetInFile[$file][$string])) {
            $this->offsetInFile[$file][$string]++;
        } else {
            $this->offsetInFile[$file][$string] = 0;
        }
        return sprintf(
            '%02s',
            base_convert($this->offsetInFile[$file][$string], 10, 36)
        );
    }
}
