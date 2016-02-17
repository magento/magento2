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

namespace PHPMD\Rule\Design;

use PHPMD\AbstractNode;
use PHPMD\AbstractRule;
use PHPMD\Node\AbstractTypeNode;
use PHPMD\Rule\ClassAware;

/**
 * This rule class will detect all classes with too much public methods.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class TooManyPublicMethods extends AbstractRule implements ClassAware
{
    /**
     * Regular expression that filters all methods that are ignored by this rule.
     *
     * @var string
     */
    private $ignoreRegexp;

    /**
     * This method checks the number of public methods with in a given class and checks
     * this number against a configured threshold.
     *
     * @param \PHPMD\AbstractNode $node
     * @return void
     */
    public function apply(AbstractNode $node)
    {
        $this->ignoreRegexp = $this->getStringProperty('ignorepattern');

        $threshold = $this->getIntProperty('maxmethods');
        if ($node->getMetric('npm') <= $threshold) {
            return;
        }
        $nom = $this->countMethods($node);
        if ($nom <= $threshold) {
            return;
        }
        $this->addViolation(
            $node,
            array(
                $node->getType(),
                $node->getName(),
                $nom,
                $threshold
            )
        );
    }

    /**
     * Counts public methods within the given class/interface node.
     *
     * @param \PHPMD\Node\AbstractTypeNode $node
     * @return integer
     */
    private function countMethods(AbstractTypeNode $node)
    {
        $count = 0;
        foreach ($node->getMethods() as $method) {
            if ($method->getNode()->isPublic() && preg_match($this->ignoreRegexp, $method->getName()) === 0) {
                ++$count;
            }
        }
        return $count;
    }
}
