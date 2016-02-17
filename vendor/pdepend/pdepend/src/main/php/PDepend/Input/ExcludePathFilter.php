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

namespace PDepend\Input;

/**
 * Filters a given file path against a blacklist with disallow path fragments.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ExcludePathFilter implements Filter
{
    /**
     * Regular expression that should not match against the relative file paths.
     *
     * @var   string
     * @since 0.10.0
     */
    protected $relative = '';

    /**
     * Regular expression that should not match against the absolute file paths.
     *
     * @var   string
     * @since 0.10.0
     */
    protected $absolute = '';

    /**
     * Constructs a new exclude path filter instance and accepts an array of
     * exclude pattern as argument.
     *
     * @param array $patterns List of exclude file path patterns.
     */
    public function __construct(array $patterns)
    {
        $quoted = array_map('preg_quote', $patterns);

        $this->relative = '(' . str_replace('\*', '.*', join('|', $quoted)) . ')i';
        $this->absolute = '(^(' . str_replace('\*', '.*', join('|', $quoted)) .'))i';
    }

    /**
     * Returns <b>true</b> if this filter accepts the given path.
     *
     * @param string $relative The relative path to the specified root.
     * @param string $absolute The absolute path to a source file.
     *
     * @return boolean
     */
    public function accept($relative, $absolute)
    {
        return ($this->notRelative($relative) && $this->notAbsolute($absolute));
    }

    /**
     * This method checks if the given <b>$path</b> does not match against the
     * exclude patterns as an absolute path.
     *
     * @param string $path The absolute path to a source file.
     *
     * @return boolean
     * @since  0.10.0
     */
    protected function notAbsolute($path)
    {
        return (preg_match($this->absolute, $path) === 0);
    }

    /**
     * This method checks if the given <b>$path</b> does not match against the
     * exclude patterns as an relative path.
     *
     * @param string $path The relative path to a source file.
     *
     * @return boolean
     * @since  0.10.0
     */
    protected function notRelative($path)
    {
        return (preg_match($this->relative, $path) === 0);
    }
}
