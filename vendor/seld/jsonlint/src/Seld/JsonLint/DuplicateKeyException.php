<?php

/*
 * This file is part of the JSON Lint package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Seld\JsonLint;

class DuplicateKeyException extends ParsingException
{
    public function __construct($message, $key, array $details = array())
    {
        $details['key'] = $key;
        parent::__construct($message, $details);
    }

    public function getKey()
    {
        return $this->details['key'];
    }
}
