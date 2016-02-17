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

namespace PDepend\Metrics;

use PDepend\Source\ASTVisitor\AbstractASTVisitor;

/**
 * This abstract class provides a base implementation of an analyzer.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
abstract class AbstractAnalyzer extends AbstractASTVisitor implements Analyzer
{
    /**
     * Global options array.
     *
     * @var array(string=>mixed)
     */
    protected $options = array();

    /**
     * List or registered listeners.
     *
     * @var \PDepend\Metrics\AnalyzerListener[]
     */
    private $listeners = array();

    /**
     * Constructs a new analyzer instance.
     *
     * @param array(string=>mixed) $options Global option array, every analyzer
     *                                      can extract the required options.
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Set global options
     *
     * @param array(string=>mixed) $options Global option array, every analyzer
     *                                      can extract the required options.
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Adds a listener to this analyzer.
     *
     * @param  \PDepend\Metrics\AnalyzerListener $listener The listener instance.
     * @return void
     */
    public function addAnalyzeListener(\PDepend\Metrics\AnalyzerListener $listener)
    {
        if (in_array($listener, $this->listeners, true) === false) {
            $this->listeners[] = $listener;
        }
    }

    /**
     * An analyzer that is active must return <b>true</b> to recognized by
     * pdepend framework, while an analyzer that does not perform any action
     * for any reason should return <b>false</b>.
     *
     * By default all analyzers are enabled. Overwrite this method to provide
     * state based disabling/enabling.
     *
     * @return boolean
     * @since  0.9.10
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * The analyzer implementation should call this method when it starts the
     * code processing. This method will send an analyzer start event to all
     * registered listeners.
     *
     * @return void
     */
    protected function fireStartAnalyzer()
    {
        foreach ($this->listeners as $listener) {
            $listener->startAnalyzer($this);
        }
    }

    /**
     * The analyzer implementation should call this method when it has finished
     * the code processing. This method will send an analyzer end event to all
     * registered listeners.
     *
     * @return void
     */
    protected function fireEndAnalyzer()
    {
        foreach ($this->listeners as $listener) {
            $listener->endAnalyzer($this);
        }
    }
}
