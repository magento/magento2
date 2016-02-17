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

namespace PDepend\Report\Jdepend;

use PDepend\Metrics\Analyzer;
use PDepend\Metrics\Analyzer\DependencyAnalyzer;
use PDepend\Report\CodeAwareGenerator;
use PDepend\Report\FileAwareGenerator;
use PDepend\Report\NoLogOutputException;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;
use PDepend\Util\Utf8Util;
use PDepend\Util\FileUtil;
use PDepend\Util\ImageConvert;

/**
 * Generates a chart with the aggregated metrics.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Chart extends AbstractASTVisitor implements CodeAwareGenerator, FileAwareGenerator
{
    /**
     * The output file name.
     *
     * @var string
     */
    private $logFile = null;

    /**
     * The context source code.
     *
     * @var \PDepend\Source\AST\ASTArtifactList
     */
    private $code = null;

    /**
     * The context analyzer instance.
     *
     * @var \PDepend\Metrics\Analyzer\DependencyAnalyzer
     */
    private $analyzer = null;

    /**
     * Sets the output log file.
     *
     * @param string $logFile The output log file.
     *
     * @return void
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }

    /**
     * Returns an <b>array</b> with accepted analyzer types. These types can be
     * concrete analyzer classes or one of the descriptive analyzer interfaces.
     *
     * @return array(string)
     */
    public function getAcceptedAnalyzers()
    {
        return array('pdepend.analyzer.dependency');
    }

    /**
     * Sets the context code nodes.
     *
     * @param  \PDepend\Source\AST\ASTArtifactList $artifacts
     * @return void
     */
    public function setArtifacts(ASTArtifactList $artifacts)
    {
        $this->code = $artifacts;
    }

    /**
     * Adds an analyzer to log. If this logger accepts the given analyzer it
     * with return <b>true</b>, otherwise the return value is <b>false</b>.
     *
     * @param  \PDepend\Metrics\Analyzer $analyzer The analyzer to log.
     * @return boolean
     */
    public function log(Analyzer $analyzer)
    {
        if ($analyzer instanceof DependencyAnalyzer) {
            $this->analyzer = $analyzer;

            return true;
        }
        return false;
    }

    /**
     * Closes the logger process and writes the output file.
     *
     * @return void
     * @throws \PDepend\Report\NoLogOutputException If the no log target exists.
     */
    public function close()
    {
        // Check for configured log file
        if ($this->logFile === null) {
            throw new NoLogOutputException($this);
        }

        $bias = 0.1;

        $svg = new \DOMDocument('1.0', 'UTF-8');
        $svg->load(dirname(__FILE__) . '/chart.svg');

        $layer = $svg->getElementById('jdepend.layer');

        $bad = $svg->getElementById('jdepend.bad');
        $bad->removeAttribute('xml:id');

        $good = $svg->getElementById('jdepend.good');
        $good->removeAttribute('xml:id');

        $legendTemplate = $svg->getElementById('jdepend.legend');
        $legendTemplate->removeAttribute('xml:id');

        $max = 0;
        $min = 0;

        $items = array();
        foreach ($this->code as $namespace) {
            if (!$namespace->isUserDefined()) {
                continue;
            }

            $metrics = $this->analyzer->getStats($namespace);

            if (count($metrics) === 0) {
                continue;
            }

            $size = $metrics['cc'] + $metrics['ac'];
            if ($size > $max) {
                $max = $size;
            } elseif ($min === 0 || $size < $min) {
                $min = $size;
            }

            $items[] = array(
                'size'         =>  $size,
                'abstraction'  =>  $metrics['a'],
                'instability'  =>  $metrics['i'],
                'distance'     =>  $metrics['d'],
                'name'         =>  Utf8Util::ensureEncoding($namespace->getName())
            );
        }

        $diff = (($max - $min) / 10);

        // Sort items by size
        usort(
            $items,
            create_function('$a, $b', 'return ($a["size"] - $b["size"]);')
        );

        foreach ($items as $item) {
            if ($item['distance'] < $bias) {
                $ellipse = $good->cloneNode(true);
            } else {
                $ellipse = $bad->cloneNode(true);
            }
            $r = 15;
            if ($diff !== 0) {
                $r = 5 + (($item['size'] - $min) / $diff);
            }

            $a = $r / 15;
            $e = (50 - $r) + ($item['abstraction'] * 320);
            $f = (20 - $r + 190) - ($item['instability'] * 190);

            $transform = "matrix({$a}, 0, 0, {$a}, {$e}, {$f})";

            $ellipse->setAttribute('id', uniqid('pdepend_'));
            $ellipse->setAttribute('title', $item['name']);
            $ellipse->setAttribute('transform', $transform);

            $layer->appendChild($ellipse);

            $result = preg_match('#\\\\([^\\\\]+)$#', $item['name'], $found);
            if ($result && count($found)) {
                $angle = rand(0, 314) / 100 - 1.57;
                $legend = $legendTemplate->cloneNode(true);
                $legend->setAttribute('x', $e + $r * (1 + cos($angle)));
                $legend->setAttribute('y', $f + $r * (1 + sin($angle)));
                $legend->nodeValue = $found[1];
                $legendTemplate->parentNode->appendChild($legend);
            }

        }

        $bad->parentNode->removeChild($bad);
        $good->parentNode->removeChild($good);
        $legendTemplate->parentNode->removeChild($legendTemplate);

        $temp  = FileUtil::getSysTempDir();
        $temp .= '/' . uniqid('pdepend_') . '.svg';
        $svg->save($temp);

        ImageConvert::convert($temp, $this->logFile);

        // Remove temp file
        unlink($temp);
    }
}
