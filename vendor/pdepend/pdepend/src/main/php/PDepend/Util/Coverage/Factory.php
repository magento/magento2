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

namespace PDepend\Util\Coverage;

/**
 * Factory used to abstract concrete coverage report formats from the pdepend
 * application.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Factory
{
    /**
     * Factory method that tries to create coverage report instance for a given
     * path name.
     *
     * @param  string $pathName Qualified path name of a coverage report file.
     * @return \PDepend\Util\Coverage\CloverReport
     * @throws \RuntimeException When the given path name does not point to a
     *         valid coverage file or onto an unsupported coverage format.
     */
    public function create($pathName)
    {
        $sxml = $this->loadXml($pathName);
        if ($sxml->project) {
            return new CloverReport($sxml);
        }
        throw new \RuntimeException('Unsupported coverage report format.');
    }

    /**
     * Creates a simple xml instance for the xml contents that are located under
     * the given path name.
     *
     * @param  string $pathName Qualified path name of a coverage report file.
     * @return \SimpleXMLElement
     * @throws \RuntimeException When the given path name does not point to a
     *         valid xml file.
     */
    private function loadXml($pathName)
    {
        $mode = libxml_use_internal_errors(true);
        $sxml = simplexml_load_file($pathName);
        libxml_use_internal_errors($mode);

        if ($sxml === false) {
            throw new \RuntimeException(trim(libxml_get_last_error()->message));
        }
        return $sxml;
    }
}
