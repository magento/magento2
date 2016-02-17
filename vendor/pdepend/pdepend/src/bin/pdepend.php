#!/usr/bin/env php
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

use PDepend\TextUI\Command;

// PEAR/svn workaround
if (strpos('@php_bin@', '@php_bin') === 0) {
    set_include_path('.' . PATH_SEPARATOR . dirname(__FILE__) . '/../main/php');
}

require_once __DIR__ . '/../../vendor/autoload.php';

// Allow as much memory as possible by default
if (extension_loaded('suhosin') && is_numeric(ini_get('suhosin.memory_limit'))) {
    $limit = ini_get('memory_limit');
    if (preg_match('(^(\d+)([BKMGT]))', $limit, $match)) {
        $shift = array('B' => 0, 'K' => 10, 'M' => 20, 'G' => 30, 'T' => 40);
        $limit = ($match[1] * (1 << $shift[$match[2]]));
    }
    if (ini_get('suhosin.memory_limit') > $limit && $limit > -1) {
        ini_set('memory_limit', ini_get('suhosin.memory_limit'));
    }
} else {
    ini_set('memory_limit', -1);
}

exit(Command::main());
