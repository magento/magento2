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

namespace PHPMD\Renderer;

use PHPMD\AbstractRenderer;
use PHPMD\Report;

/**
 * This renderer output a simple html file with all found violations and suspect
 * software artifacts.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class HTMLRenderer extends AbstractRenderer
{
    /**
     * This method will be called on all renderers before the engine starts the
     * real report processing.
     *
     * @return void
     */
    public function start()
    {
        $writer = $this->getWriter();

        $writer->write('<html><head><title>PHPMD</title></head><body>');
        $writer->write(PHP_EOL);
        $writer->write('<center><h1>PHPMD report</h1></center>');
        $writer->write('<center><h2>Problems found</h2></center>');
        $writer->write(PHP_EOL);
        $writer->write('<table align="center" cellspacing="0" cellpadding="3">');
        $writer->write('<tr>');
        $writer->write('<th>#</th><th>File</th><th>Line</th><th>Problem</th>');
        $writer->write('</tr>');
        $writer->write(PHP_EOL);
    }

    /**
     * This method will be called when the engine has finished the source analysis
     * phase.
     *
     * @param \PHPMD\Report $report
     * @return void
     */
    public function renderReport(Report $report)
    {
        $index = 0;

        $writer = $this->getWriter();
        foreach ($report->getRuleViolations() as $violation) {
            $writer->write('<tr');
            if (++$index % 2 === 1) {
                $writer->write(' bgcolor="lightgrey"');
            }
            $writer->write('>');
            $writer->write(PHP_EOL);

            $writer->write('<td align="center">');
            $writer->write($index);
            $writer->write('</td>');
            $writer->write(PHP_EOL);

            $writer->write('<td>');
            $writer->write(htmlentities($violation->getFileName()));
            $writer->write('</td>');
            $writer->write(PHP_EOL);

            $writer->write('<td align="center" width="5%">');
            $writer->write($violation->getBeginLine());
            $writer->write('</td>');
            $writer->write(PHP_EOL);

            $writer->write('<td>');
            if ($violation->getRule()->getExternalInfoUrl()) {
                $writer->write('<a href="');
                $writer->write($violation->getRule()->getExternalInfoUrl());
                $writer->write('">');
            }

            $writer->write(htmlentities($violation->getDescription()));
            if ($violation->getRule()->getExternalInfoUrl()) {
                $writer->write('</a>');
            }

            $writer->write('</td>');
            $writer->write(PHP_EOL);

            $writer->write('</tr>');
            $writer->write(PHP_EOL);
        }

        $writer->write('</table>');

        $this->glomProcessingErrors($report);
    }

    /**
     * This method will be called the engine has finished the report processing
     * for all registered renderers.
     *
     * @return void
     */
    public function end()
    {
        $writer = $this->getWriter();
        $writer->write('</body></html>');
    }

    /**
     * This method will render a html table with occurred processing errors.
     *
     * @param \PHPMD\Report $report
     * @return void
     * @since 1.2.1
     */
    private function glomProcessingErrors(Report $report)
    {
        if (false === $report->hasErrors()) {
            return;
        }

        $writer = $this->getWriter();

        $writer->write('<hr />');
        $writer->write('<center><h3>Processing errors</h3></center>');
        $writer->write('<table align="center" cellspacing="0" cellpadding="3">');
        $writer->write('<tr><th>File</th><th>Problem</th></tr>');

        $index = 0;
        foreach ($report->getErrors() as $error) {
            $writer->write('<tr');
            if (++$index % 2 === 1) {
                $writer->write(' bgcolor="lightgrey"');
            }
            $writer->write('>');
            $writer->write('<td>' . $error->getFile() . '</td>');
            $writer->write('<td>' . htmlentities($error->getMessage()) . '</td>');
            $writer->write('</tr>' . PHP_EOL);
        }

        $writer->write('</table>');
    }
}
