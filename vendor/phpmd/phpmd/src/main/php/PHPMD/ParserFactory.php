<?php
/**
 * This file is part of PHP Mess Detector.
 *
 * Copyright (c) 2008-2012, Manuel Pichler <mapi@phpmd.org>.
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
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD;

use PDepend\Application;
use PDepend\Engine;
use PDepend\Input\ExcludePathFilter;
use PDepend\Input\ExtensionFilter;

/**
 * Simple factory that is used to return a ready to use PDepend instance.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ParserFactory
{
    /**
     * Mapping between phpmd option names and those used by pdepend.
     *
     * @var array
     */
    private $phpmd2pdepend = array(
        'coverage'  =>  'coverage-report'
    );

    /**
     * Creates the used {@link \PHPMD\Parser} analyzer instance.
     *
     * @param \PHPMD\PHPMD $phpmd
     * @return \PHPMD\Parser
     */
    public function create(PHPMD $phpmd)
    {
        $pdepend = $this->createInstance();
        $pdepend = $this->init($pdepend, $phpmd);

        return new Parser($pdepend);
    }

    /**
     * Creates a clean php depend instance with some base settings.
     *
     * @return \PDepend\Engine
     */
    private function createInstance()
    {
        $application = new Application();

        if (file_exists(getcwd() . '/pdepend.xml')) {
            $application->setConfigurationFile(getcwd() . '/pdepend.xml');
        } elseif (file_exists(getcwd() . '/pdepend.xml.dist')) {
            $application->setConfigurationFile(getcwd() . '/pdepend.xml.dist');
        }

        return $application->getEngine();
    }

    /**
     * Configures the given PDepend\Engine instance based on some user settings.
     *
     * @param \PDepend\Engine $pdepend
     * @param \PHPMD\PHPMD $phpmd
     * @return \PDepend\Engine
     */
    private function init(Engine $pdepend, PHPMD $phpmd)
    {
        $this->initOptions($pdepend, $phpmd);
        $this->initInput($pdepend, $phpmd);
        $this->initIgnores($pdepend, $phpmd);
        $this->initExtensions($pdepend, $phpmd);

        return $pdepend;
    }

    /**
     * Configures the input source.
     *
     * @param \PDepend\Engine $pdepend
     * @param \PHPMD\PHPMD $phpmd
     * @return void
     */
    private function initInput(Engine $pdepend, PHPMD $phpmd)
    {
        foreach (explode(',', $phpmd->getInput()) as $path) {
            if (is_dir(trim($path))) {
                $pdepend->addDirectory(trim($path));
            } else {
                $pdepend->addFile(trim($path));
            }
        }
    }

    /**
     * Initializes the ignored files and path's.
     *
     * @param \PDepend\Engine $pdepend
     * @param \PHPMD\PHPMD $phpmd
     * @return void
     */
    private function initIgnores(Engine $pdepend, PHPMD $phpmd)
    {
        if (count($phpmd->getIgnorePattern()) > 0) {
            $pdepend->addFileFilter(
                new ExcludePathFilter($phpmd->getIgnorePattern())
            );
        }
    }

    /**
     * Initializes the accepted php source file extensions.
     *
     * @param \PDepend\Engine $pdepend
     * @param \PHPMD\PHPMD $phpmd
     * @return void
     */
    private function initExtensions(Engine $pdepend, PHPMD $phpmd)
    {
        if (count($phpmd->getFileExtensions()) > 0) {
            $pdepend->addFileFilter(
                new ExtensionFilter($phpmd->getFileExtensions())
            );
        }
    }

    /**
     * Initializes additional options for pdepend.
     *
     * @param \PDepend\Engine $pdepend
     * @param \PHPMD\PHPMD $phpmd
     * @return void
     */
    private function initOptions(Engine $pdepend, PHPMD $phpmd)
    {
        $options = array();
        foreach (array_filter($phpmd->getOptions()) as $name => $value) {
            if (isset($this->phpmd2pdepend[$name])) {
                $options[$this->phpmd2pdepend[$name]] = $value;
            }
        }
        $pdepend->setOptions($options);
    }
}
