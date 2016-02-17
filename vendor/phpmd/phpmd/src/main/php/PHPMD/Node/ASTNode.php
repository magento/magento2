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

namespace PHPMD\Node;

use PHPMD\Rule;

/**
 * Wrapper around a PHP_Depend ast node.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ASTNode extends \PHPMD\AbstractNode
{
    /**
     * The source file of this node.
     *
     * @var string
     */
    private $fileName = null;

    /**
     * Constructs a new ast node instance.
     *
     * @param \PDepend\Source\AST\ASTNode $node
     * @param string $fileName
     */
    public function __construct(\PDepend\Source\AST\ASTNode $node, $fileName)
    {
        parent::__construct($node);

        $this->fileName = $fileName;
    }

    /**
     * Checks if this node has a suppressed annotation for the given rule
     * instance.
     *
     * @param \PHPMD\Rule $rule
     * @return boolean
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     */
    public function hasSuppressWarningsAnnotationFor(Rule $rule)
    {
        return false;
    }

    /**
     * Returns the source name for this node, maybe a class or interface name,
     * or a package, method, function name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->getImage();
    }

    /**
     * Returns the image of the underlying node.
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getNode()->getImage();
    }

    /**
     * Returns the name of the declaring source file.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Returns the name of the parent type or <b>null</b> when this node has no
     * parent type.
     *
     * @return string
     */
    public function getParentName()
    {
        return null;
    }

    /**
     * Returns the name of the parent namespace.
     *
     * @return string
     */
    public function getNamespaceName()
    {
        return null;
    }
}
