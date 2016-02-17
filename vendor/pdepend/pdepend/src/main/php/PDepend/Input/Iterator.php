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
 * Simple utility filter iterator for php source files.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Iterator extends \FilterIterator
{
    /**
     * The associated filter object.
     *
     * @var \PDepend\Input\Filter
     */
    protected $filter = null;

    /**
     * Optional root path for the files.
     *
     * @var   string
     * @since 0.10.0
     */
    protected $rootPath = null;

    /**
     * Constructs a new file filter iterator.
     *
     * @param \Iterator             $iterator The inner iterator.
     * @param \PDepend\Input\Filter $filter   The filter object.
     * @param string                $rootPath Optional root path for the files.
     */
    public function __construct(\Iterator $iterator, Filter $filter, $rootPath = null)
    {
        parent::__construct($iterator);

        $this->filter   = $filter;
        $this->rootPath = $rootPath;
    }

    /**
     * Returns <b>true</b> if the file name ends with '.php'.
     *
     * @return boolean
     */
    public function accept()
    {
        return $this->filter->accept($this->getLocalPath(), $this->getFullPath());
    }

    /**
     * Returns the full qualified realpath for the currently active file.
     *
     * @return string
     * @since  0.10.0
     */
    protected function getFullPath()
    {
        return $this->getInnerIterator()->current()->getRealpath();
    }

    /**
     * Returns the local path of the current file, if the root path property was
     * set. If not, this method returns the absolute file path.
     *
     * @return string
     * @since  0.10.0
     */
    protected function getLocalPath()
    {
        if ($this->rootPath && 0 === strpos($this->getFullPath(), $this->rootPath)) {
            return substr($this->getFullPath(), strlen($this->rootPath));
        }
        return $this->getFullPath();
    }
}
