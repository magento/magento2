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
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTNamespace;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;
use PDepend\Util\Utf8Util;

/**
 * Generates an xml document with the aggregated metrics. The format is borrowed
 * from <a href="http://clarkware.com/software/JDepend.html">JDepend</a>.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Xml extends AbstractASTVisitor implements CodeAwareGenerator, FileAwareGenerator
{
    /**
     * The output log file.
     *
     * @var string
     */
    private $logFile = null;

    /**
     * The raw {@link \PDepend\Source\AST\ASTNamespace} instances.
     *
     * @var \PDepend\Source\AST\ASTArtifactList
     */
    protected $code = null;

    /**
     * Set of all analyzed files.
     *
     * @var \PDepend\Source\AST\ASTCompilationUnit[]
     */
    protected $fileSet = array();

    /**
     * List of all generated project metrics.
     *
     * @var array(string=>mixed)
     */
    protected $projectMetrics = array();

    /**
     * List of all collected node metrics.
     *
     * @var array(string=>array)
     */
    protected $nodeMetrics = array();

    /**
     * The dependency result set.
     *
     * @var DependencyAnalyzer
     */
    protected $analyzer = null;

    /**
     * The Packages dom element.
     *
     * @var \DOMElement
     */
    protected $packages = null;

    /**
     * The Cycles dom element.
     *
     * @var \DOMElement
     */
    protected $cycles = null;

    /**
     * The concrete classes element for the current package.
     *
     * @var \DOMElement
     */
    protected $concreteClasses = null;

    /**
     * The abstract classes element for the current package.
     *
     * @var \DOMElement
     */
    protected $abstractClasses = null;

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
        // Check for configured output
        if ($this->logFile === null) {
            throw new NoLogOutputException($this);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $dom->formatOutput = true;

        $jdepend = $dom->createElement('PDepend');

        $this->packages = $jdepend->appendChild($dom->createElement('Packages'));
        $this->cycles   = $jdepend->appendChild($dom->createElement('Cycles'));

        foreach ($this->code as $node) {
            $node->accept($this);
        }

        $dom->appendChild($jdepend);
        $dom->save($this->logFile);
    }

    /**
     * Visits a class node.
     *
     * @param  \PDepend\Source\AST\ASTClass $class
     * @return void
     */
    public function visitClass(ASTClass $class)
    {
        if (!$class->isUserDefined()) {
            return;
        }

        $doc = $this->packages->ownerDocument;

        $classXml = $doc->createElement('Class');
        $classXml->setAttribute('sourceFile', (string) $class->getCompilationUnit());
        $classXml->appendChild(
            $doc->createTextNode(
                Utf8Util::ensureEncoding($class->getName())
            )
        );

        if ($class->isAbstract()) {
            $this->abstractClasses->appendChild($classXml);
        } else {
            $this->concreteClasses->appendChild($classXml);
        }
    }

    /**
     * Visits a code interface object.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function visitInterface(ASTInterface $interface)
    {
        if (!$interface->isUserDefined()) {
            return;
        }

        $doc = $this->abstractClasses->ownerDocument;

        $classXml = $doc->createElement('Class');
        $classXml->setAttribute('sourceFile', (string) $interface->getCompilationUnit());
        $classXml->appendChild(
            $doc->createTextNode(
                Utf8Util::ensureEncoding($interface->getName())
            )
        );

        $this->abstractClasses->appendChild($classXml);
    }

    /**
     * Visits a package node.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespace
     * @return void
     */
    public function visitNamespace(ASTNamespace $namespace)
    {
        if (!$namespace->isUserDefined()) {
            return;
        }

        $stats = $this->analyzer->getStats($namespace);
        if (count($stats) === 0) {
            return;
        }

        $doc = $this->packages->ownerDocument;

        $this->concreteClasses = $doc->createElement('ConcreteClasses');
        $this->abstractClasses = $doc->createElement('AbstractClasses');

        $packageXml = $doc->createElement('Package');
        $packageXml->setAttribute('name', Utf8Util::ensureEncoding($namespace->getName()));

        $statsXml = $doc->createElement('Stats');
        $statsXml->appendChild($doc->createElement('TotalClasses'))
            ->appendChild($doc->createTextNode($stats['tc']));
        $statsXml->appendChild($doc->createElement('ConcreteClasses'))
            ->appendChild($doc->createTextNode($stats['cc']));
        $statsXml->appendChild($doc->createElement('AbstractClasses'))
            ->appendChild($doc->createTextNode($stats['ac']));
        $statsXml->appendChild($doc->createElement('Ca'))
            ->appendChild($doc->createTextNode($stats['ca']));
        $statsXml->appendChild($doc->createElement('Ce'))
            ->appendChild($doc->createTextNode($stats['ce']));
        $statsXml->appendChild($doc->createElement('A'))
            ->appendChild($doc->createTextNode($stats['a']));
        $statsXml->appendChild($doc->createElement('I'))
            ->appendChild($doc->createTextNode($stats['i']));
        $statsXml->appendChild($doc->createElement('D'))
            ->appendChild($doc->createTextNode($stats['d']));

        $dependsUpon = $doc->createElement('DependsUpon');
        foreach ($this->analyzer->getEfferents($namespace) as $efferent) {
            $efferentXml = $doc->createElement('Package');
            $efferentXml->appendChild(
                $doc->createTextNode(
                    Utf8Util::ensureEncoding($efferent->getName())
                )
            );

            $dependsUpon->appendChild($efferentXml);
        }

        $usedBy = $doc->createElement('UsedBy');
        foreach ($this->analyzer->getAfferents($namespace) as $afferent) {
            $afferentXml = $doc->createElement('Package');
            $afferentXml->appendChild(
                $doc->createTextNode(
                    Utf8Util::ensureEncoding($afferent->getName())
                )
            );

            $usedBy->appendChild($afferentXml);
        }

        $packageXml->appendChild($statsXml);
        $packageXml->appendChild($this->concreteClasses);
        $packageXml->appendChild($this->abstractClasses);
        $packageXml->appendChild($dependsUpon);
        $packageXml->appendChild($usedBy);

        if (($cycles = $this->analyzer->getCycle($namespace)) !== null) {
            $cycleXml = $doc->createElement('Package');
            $cycleXml->setAttribute('Name', Utf8Util::ensureEncoding($namespace->getName()));

            foreach ($cycles as $cycle) {
                $cycleXml->appendChild($doc->createElement('Package'))
                    ->appendChild(
                        $doc->createTextNode(
                            Utf8Util::ensureEncoding($cycle->getName())
                        )
                    );
            }

            $this->cycles->appendChild($cycleXml);
        }

        foreach ($namespace->getTypes() as $type) {
            $type->accept($this);
        }

        if ($this->concreteClasses->firstChild === null
            && $this->abstractClasses->firstChild === null
        ) {
            return;
        }

        $this->packages->appendChild($packageXml);
    }
}
