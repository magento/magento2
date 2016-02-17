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
use PHPMD\PHPMD;
use PHPMD\Report;

/**
 * This class will render a Java-PMD compatible xml-report.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class XMLRenderer extends AbstractRenderer
{
    /**
     * Temporary property that holds the name of the last rendered file, it is
     * used to detect the next processed file.
     *
     * @var string
     */
    private $fileName = null;

    /**
     * This method will be called on all renderers before the engine starts the
     * real report processing.
     *
     * @return void
     */
    public function start()
    {
        $this->getWriter()->write('<?xml version="1.0" encoding="UTF-8" ?>');
        $this->getWriter()->write(PHP_EOL);
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
        $writer = $this->getWriter();
        $writer->write('<pmd version="' . PHPMD::VERSION . '" ');
        $writer->write('timestamp="' . date('c') . '">');
        $writer->write(PHP_EOL);

        foreach ($report->getRuleViolations() as $violation) {
            $fileName = $violation->getFileName();
            
            if ($this->fileName !== $fileName) {
                // Not first file
                if ($this->fileName !== null) {
                    $writer->write('  </file>' . PHP_EOL);
                }
                // Store current file name
                $this->fileName = $fileName;

                $writer->write('  <file name="' . $fileName . '">' . PHP_EOL);
            }

            $rule = $violation->getRule();

            $writer->write('    <violation');
            $writer->write(' beginline="' . $violation->getBeginLine() . '"');
            $writer->write(' endline="' . $violation->getEndLine() . '"');
            $writer->write(' rule="' . $rule->getName() . '"');
            $writer->write(' ruleset="' . $rule->getRuleSetName() . '"');
            
            $this->maybeAdd('package', $violation->getNamespaceName());
            $this->maybeAdd('externalInfoUrl', $rule->getExternalInfoUrl());
            $this->maybeAdd('function', $violation->getFunctionName());
            $this->maybeAdd('class', $violation->getClassName());
            $this->maybeAdd('method', $violation->getMethodName());
            //$this->_maybeAdd('variable', $violation->getVariableName());

            $writer->write(' priority="' . $rule->getPriority() . '"');
            $writer->write('>' . PHP_EOL);
            $writer->write('      ' . $violation->getDescription() . PHP_EOL);
            $writer->write('    </violation>' . PHP_EOL);
        }

        // Last file and at least one violation
        if ($this->fileName !== null) {
            $writer->write('  </file>' . PHP_EOL);
        }

        foreach ($report->getErrors() as $error) {
            $writer->write('  <error filename="');
            $writer->write($error->getFile());
            $writer->write('" msg="');
            $writer->write(htmlspecialchars($error->getMessage()));
            $writer->write('" />' . PHP_EOL);
        }

        $writer->write('</pmd>' . PHP_EOL);
    }

    /**
     * This method will write a xml attribute named <b>$attr</b> to the output
     * when the given <b>$value</b> is not an empty string and is not <b>null</b>.
     *
     * @param string $attr  The xml attribute name.
     * @param string $value The attribute value.
     *
     * @return void
     */
    private function maybeAdd($attr, $value)
    {
        if ($value === null || trim($value) === '') {
            return;
        }
        $this->getWriter()->write(' ' . $attr . '="' . $value . '"');
    }
}
