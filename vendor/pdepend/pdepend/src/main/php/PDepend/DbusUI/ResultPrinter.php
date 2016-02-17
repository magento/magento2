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

namespace PDepend\DbusUI;

// This is just fun and it is not really testable
// @codeCoverageIgnoreStart
use PDepend\Metrics\Analyzer;
use PDepend\ProcessListener;
use PDepend\Source\ASTVisitor\AbstractASTVisitListener;
use PDepend\Source\Builder\Builder;
use PDepend\Source\Tokenizer\Tokenizer;

/**
 * Fun result printer that uses dbus to show a notification window.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ResultPrinter extends AbstractASTVisitListener implements ProcessListener
{
    /**
     * Time when it the process has started.
     *
     * @var integer
     */
    private $startTime = 0;

    /**
     * Number of parsed/analyzed files.
     *
     * @var integer
     */
    private $parsedFiles = 0;

    /**
     * Is called when PDepend starts the file parsing process.
     *
     * @param  \PDepend\Source\Builder\Builder $builder The used node builder instance.
     * @return void
     */
    public function startParseProcess(Builder $builder)
    {
        $this->startTime = time();
    }

    /**
     * Is called when PDepend has finished the file parsing process.
     *
     * @param  \PDepend\Source\Builder\Builder $builder The used node builder instance.
     * @return void
     */
    public function endParseProcess(Builder $builder)
    {
    }

    /**
     * Is called when PDepend starts parsing of a new file.
     *
     * @param  \PDepend\Source\Tokenizer\Tokenizer $tokenizer
     * @return void
     */
    public function startFileParsing(Tokenizer $tokenizer)
    {
    }

    /**
     * Is called when PDepend has finished a file.
     *
     * @param  \PDepend\Source\Tokenizer\Tokenizer $tokenizer
     * @return void
     */
    public function endFileParsing(Tokenizer $tokenizer)
    {
        ++$this->parsedFiles;
    }

    /**
     * Is called when PDepend starts the analyzing process.
     *
     * @return void
     */
    public function startAnalyzeProcess()
    {
    }

    /**
     * Is called when PDepend has finished the analyzing process.
     *
     * @return void
     */
    public function endAnalyzeProcess()
    {
    }

    /**
     * Is called when PDepend starts the logging process.
     *
     * @return void
     */
    public function startLogProcess()
    {
    }

    /**
     * Is called when PDepend has finished the logging process.
     *
     * @return void
     */
    public function endLogProcess()
    {
        if (extension_loaded('dbus') === false) {
            return;
        }

        $dbus  = new Dbus(Dbus::BUS_SESSION);
        $proxy = $dbus->createProxy(
            "org.freedesktop.Notifications", // connection name
            "/org/freedesktop/Notifications", // object
            "org.freedesktop.Notifications" // interface
        );
        $proxy->Notify(
            'PDepend',
            new DBusUInt32(0),
            'pdepend',
            'PDepend',
            sprintf(
                '%d files analyzed in %s minutes...',
                $this->parsedFiles,
                (date('i:s', time() - $this->startTime))
            ),
            new DBusArray(DBus::STRING, array()),
            new DBusDict(DBus::VARIANT, array()),
            1000
        );
    }

    /**
     * Is called when PDepend starts a new analyzer.
     *
     * @param  \PDepend\Metrics\Analyzer $analyzer The context analyzer instance.
     * @return void
     */
    public function startAnalyzer(Analyzer $analyzer)
    {
    }

    /**
     * Is called when PDepend has finished one analyzing process.
     *
     * @param  \PDepend\Metrics\Analyzer $analyzer The context analyzer instance.
     * @return void
     */
    public function endAnalyzer(Analyzer $analyzer)
    {
    }
}

// @codeCoverageIgnoreEnd
