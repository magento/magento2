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

namespace PDepend\Report\Dependencies;

use PDepend\Metrics\Analyzer;
use PDepend\Metrics\Analyzer\ClassDependencyAnalyzer;
use PDepend\Report\CodeAwareGenerator;
use PDepend\Report\FileAwareGenerator;
use PDepend\Report\NoLogOutputException;
use PDepend\Source\AST\AbstractASTArtifact;
use PDepend\Source\AST\ASTArtifactList;
use PDepend\Source\AST\AbstractASTClassOrInterface;
use PDepend\Source\AST\ASTClass;
use PDepend\Source\AST\ASTCompilationUnit;
use PDepend\Source\AST\ASTFunction;
use PDepend\Source\AST\ASTInterface;
use PDepend\Source\AST\ASTMethod;
use PDepend\Source\AST\ASTNamespace;
use PDepend\Source\AST\ASTTrait;
use PDepend\Source\ASTVisitor\AbstractASTVisitor;
use PDepend\Util\Utf8Util;

/**
 * This logger generates a summary xml document with aggregated project, class,
 * method and file dependencies.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Xml extends AbstractASTVisitor implements CodeAwareGenerator, FileAwareGenerator
{
    /**
     * The log output file.
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
     * @var \PDepend\Metrics\Analyzer\DependencyAnalyzer
     */
    private $dependencyAnalyzer;

    /**
     * The internal used xml stack.
     *
     * @var DOMElement[]
     */
    private $xmlStack = array();

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
        return array(
            'pdepend.analyzer.class_dependency',
        );
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
     * @param  \PDepend\dependencies\Analyzer $analyzer The analyzer to log.
     * @return boolean
     */
    public function log(Analyzer $analyzer)
    {
        if ($analyzer instanceof ClassDependencyAnalyzer) {
            $this->dependencyAnalyzer = $analyzer;
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
        if ($this->logFile === null) {
            throw new NoLogOutputException($this);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $dependencies = $dom->createElement('dependencies');
        $dependencies->setAttribute('generated', date('Y-m-d\TH:i:s'));
        $dependencies->setAttribute('pdepend', '@package_version@');
        array_push($this->xmlStack, $dependencies);

        foreach ($this->code as $node) {
            $node->accept($this);
        }

        $dom->appendChild($dependencies);
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
        $this->generateTypeXml($class, 'class');
    }

    /**
     * Visits a trait node.
     *
     * @param  \PDepend\Source\AST\ASTTrait $trait
     * @return void
     */
    public function visitTrait(ASTTrait $trait)
    {
        $this->generateTypeXml($trait, 'trait');
    }

    /**
     * Generates the XML for a class or trait node.
     *
     * @param  \PDepend\Source\AST\ASTClass $type
     * @param  string                       $typeIdentifier
     * @return void
     */
    private function generateTypeXml(AbstractASTClassOrInterface $type, $typeIdentifier)
    {
        if (!$type->isUserDefined()) {
            return;
        }

        $xml = end($this->xmlStack);
        $doc = $xml->ownerDocument;

        $typeXml = $doc->createElement($typeIdentifier);
        $typeXml->setAttribute('name', Utf8Util::ensureEncoding($type->getName()));
        $xml->appendChild($typeXml);

        array_push($this->xmlStack, $typeXml);
        $this->writeNodeDependencies($typeXml, $type);
        $this->writeFileReference($typeXml, $type->getCompilationUnit());
        array_pop($this->xmlStack);
    }

    /**
     * Visits a function node.
     *
     * @param  \PDepend\Source\AST\ASTFunction $function
     * @return void
     */
    public function visitFunction(ASTFunction $function)
    {
        // Do not care
    }

    /**
     * Visits a code interface object.
     *
     * @param  \PDepend\Source\AST\ASTInterface $interface
     * @return void
     */
    public function visitInterface(ASTInterface $interface)
    {
        $this->generateTypeXml($interface, 'interface');
    }

    /**
     * Visits a namespace node.
     *
     * @param  \PDepend\Source\AST\ASTNamespace $namespace
     * @return void
     */
    public function visitNamespace(ASTNamespace $namespace)
    {
        $xml = end($this->xmlStack);
        $doc = $xml->ownerDocument;

        $packageXml = $doc->createElement('package');
        $packageXml->setAttribute('name', Utf8Util::ensureEncoding($namespace->getName()));

        array_push($this->xmlStack, $packageXml);

        foreach ($namespace->getTypes() as $type) {
            $type->accept($this);
        }
        foreach ($namespace->getFunctions() as $function) {
            $function->accept($this);
        }

        array_pop($this->xmlStack);

        if ($packageXml->firstChild === null) {
            return;
        }

        $xml->appendChild($packageXml);
    }

    /**
     * Aggregates all dependencies for the given <b>$node</b> instance and adds them
     * to the <b>\DOMElement</b>
     *
     * @param  \DOMElement                             $xml
     * @param  \PDepend\Source\AST\AbstractASTArtifact $node
     * @return void
     */
    protected function writeNodeDependencies(\DOMElement $xml, AbstractASTArtifact $node)
    {
        if (!$this->dependencyAnalyzer) {
            return;
        }

        $xml = end($this->xmlStack);
        $doc = $xml->ownerDocument;

        $efferentXml = $doc->createElement('efferent');
        $xml->appendChild($efferentXml);
        foreach ($this->dependencyAnalyzer->getEfferents($node) as $type) {
            $typeXml = $doc->createElement('type');
            $typeXml->setAttribute('namespace', Utf8Util::ensureEncoding($type->getNamespaceName()));
            $typeXml->setAttribute('name', Utf8Util::ensureEncoding($type->getName()));

            $efferentXml->appendChild($typeXml);
        }

        $afferentXml = $doc->createElement('afferent');
        $xml->appendChild($afferentXml);
        foreach ($this->dependencyAnalyzer->getAfferents($node) as $type) {
            $typeXml = $doc->createElement('type');
            $typeXml->setAttribute('namespace', Utf8Util::ensureEncoding($type->getNamespaceName()));
            $typeXml->setAttribute('name', Utf8Util::ensureEncoding($type->getName()));

            $afferentXml->appendChild($typeXml);
        }
    }

    /**
     * Appends a file reference element to the given <b>$xml</b> element.
     *
     * <code>
     *   <class name="\PDepend\Engine">
     *     <file name="PDepend/Engine.php" />
     *   </class>
     * </code>
     *
     * @param  \DOMElement                            $xml             The parent xml element.
     * @param  \PDepend\Source\AST\ASTCompilationUnit $compilationUnit The code file instance.
     * @return void
     */
    protected function writeFileReference(\DOMElement $xml, ASTCompilationUnit $compilationUnit = null)
    {
        if (in_array($compilationUnit, $this->fileSet, true) === false) {
            $this->fileSet[] = $compilationUnit;
        }

        $fileXml = $xml->ownerDocument->createElement('file');
        $fileXml->setAttribute('name', Utf8Util::ensureEncoding($compilationUnit->getFileName()));

        $xml->appendChild($fileXml);
    }
}
