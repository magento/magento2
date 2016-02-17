<?php
/**
 * This file is part of PHP Mess Detector.
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@phpmd.org>.
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
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PHPMD\Rule\Design;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\MethodNode;
use PHPMD\Rule\FunctionAware;
use PHPMD\Rule\MethodAware;

/**
 * This rule class detects possible development code fragments that were left
 * into the code.
 *
 * @author Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @see https://github.com/phpmd/phpmd/issues/265
 * @since 2.3.0
 */
class DevelopmentCodeFragment extends AbstractRule implements MethodAware, FunctionAware
{
    /**
     * This method checks if a given function or method contains an eval-expression
     * and emits a rule violation when it exists.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        foreach ($node->findChildrenOfType('FunctionPostfix') as $postfix) {
            $image = strtolower($postfix->getImage());
            if (false === in_array($image, $this->getSuspectImages())) {
                continue;
            }

            $image = $node->getImage();
            if ($node instanceof MethodNode) {
                $image = sprintf('%s::%s', $node->getParentName(), $node->getImage());
            }

            $this->addViolation($postfix, array($node->getType(), $image, $postfix->getImage()));
        }
    }

    /**
     * Returns an array with function images that are normally only used during
     * development.
     *
     * @return array
     */
    private function getSuspectImages()
    {
        return array_map(
            'strtolower',
            array_map(
                'trim',
                explode(
                    ',',
                    $this->getStringProperty('unwanted-functions')
                )
            )
        );
    }
}
