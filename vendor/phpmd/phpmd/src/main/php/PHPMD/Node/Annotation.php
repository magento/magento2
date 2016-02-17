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
 * Simple code annotation class.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Annotation
{
    /**
     * Name of the suppress warnings annotation.
     */
    const SUPPRESS_ANNOTATION = 'SuppressWarnings';

    /**
     * The annotation name.
     *
     * @var string
     */
    private $name = null;

    /**
     * The annotation value.
     *
     * @var string
     */
    private $value = null;

    /**
     * Constructs a new annotation instance.
     *
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value)
    {
        $this->name  = $name;
        $this->value = trim($value, '" ');
    }

    /**
     * Checks if this annotation suppresses the given rule.
     *
     * @param \PHPMD\Rule $rule
     * @return boolean
     */
    public function suppresses(Rule $rule)
    {
        if ($this->name === self::SUPPRESS_ANNOTATION) {
            return $this->isSuppressed($rule);
        }
        return false;
    }

    /**
     * Checks if this annotation suppresses the given rule.
     *
     * @param \PHPMD\Rule $rule
     * @return boolean
     */
    private function isSuppressed(Rule $rule)
    {
        if (in_array($this->value, array('PHPMD', 'PMD'))) {
            return true;
        } elseif (strpos($this->value, 'PMD.' . $rule->getName()) !== false) {
            return true;
        }
        return (stripos($rule->getName(), $this->value) !== false);
    }
}
