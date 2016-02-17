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

namespace PDepend\Util\Coverage;

use PDepend\Source\AST\AbstractASTArtifact;

/**
 * Coverage report implementation for clover formatted xml files.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class CloverReport implements Report
{
    /**
     * Holds the line coverage for all files found in the coverage report.
     *
     * @var array(string=>array)
     */
    private $fileLineCoverage = array();

    /**
     * Constructs a new clover report instance.
     *
     * @param \SimpleXMLElement $sxml The context simple xml element.
     */
    public function __construct(\SimpleXMLElement $sxml)
    {
        $this->readProjectCoverage($sxml->project);
    }

    /**
     * Reads the coverage information for a project.
     *
     * @param  \SimpleXMLElement $sxml Element representing the clover project tag.
     * @return void
     */
    private function readProjectCoverage(\SimpleXMLElement $sxml)
    {
        $this->readFileCoverage($sxml);
        foreach ($sxml->package as $package) {
            $this->readFileCoverage($package);
        }
    }

    /**
     * Reads the coverage information for all file elements under the given
     * parent.
     *
     * @param  \SimpleXMLElement $sxml Element representing a file parent element.
     * @return void
     */
    private function readFileCoverage(\SimpleXMLElement $sxml)
    {
        foreach ($sxml->file as $file) {
            $lines = array();
            foreach ($file->line as $line) {
                $lines[(int) $line['num']] = (0 < (int) $line['count']);
            }
            $this->fileLineCoverage[(string) $file['name']] = $lines;
        }
    }

    /**
     * Returns the percentage code coverage for the given item instance.
     *
     * @param  \PDepend\Source\AST\AbstractASTArtifact $artifact
     * @return float
     */
    public function getCoverage(AbstractASTArtifact $artifact)
    {
        $lines = $this->getLines($artifact->getCompilationUnit()->getFileName());

        $startLine = $artifact->getStartLine();
        $endLine   = $artifact->getEndLine();

        $executable = 0;
        $executed   = 0;
        for ($i = $startLine; $i <= $endLine; ++$i) {
            if (!isset($lines[$i])) {
                continue;
            }
            ++$executable;
            if ($lines[$i]) {
                ++$executed;
            }
        }

        if (0 === $executed && 1 === $executable && 0 < ($endLine - $startLine)) {
            return 100;
        }
        if ($executed === 0) {
            return 0;
        }
        return (($executed / $executable) * 100);
    }

    /**
     * Returns the lines of the covered file.
     *
     * @param  string $fileName The source file name.
     * @return array(boolean)
     */
    private function getLines($fileName)
    {
        if (isset($this->fileLineCoverage[$fileName])) {
            return $this->fileLineCoverage[$fileName];
        }
        return array();
    }
}
