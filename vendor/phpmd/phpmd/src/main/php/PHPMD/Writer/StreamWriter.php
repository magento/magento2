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

namespace PHPMD\Writer;

use PHPMD\AbstractWriter;

/**
 * This writer uses PHP's stream api as its output target.
 *
 * @author    Manuel Pichler <mapi@phpmd.org>
 * @copyright 2008-2014 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class StreamWriter extends AbstractWriter
{
    /**
     * The stream resource handle
     *
     * @var resource
     */
    private $stream = null;

    /**
     * Constructs a new stream writer instance.
     *
     * @param resource|string $streamResourceOrUri
     */
    public function __construct($streamResourceOrUri)
    {
        if (is_resource($streamResourceOrUri) === true) {
            $this->stream = $streamResourceOrUri;
        } else {
            $dirName = dirname($streamResourceOrUri);
            if (file_exists($dirName) === false) {
                mkdir($dirName, 0777, true);
            }
            if (file_exists($dirName) === false) {
                $message = 'Cannot find output directory "' . $dirName . '".';
                throw new \RuntimeException($message);
            }

            $this->stream = fopen($streamResourceOrUri, 'wb');
        }
    }

    /**
     * The dtor closes the open output resource.
     */
    public function __destruct()
    {
        if ($this->stream !== STDOUT && is_resource($this->stream) === true) {
            @fclose($this->stream);
        }
        $this->stream = null;
    }

    /**
     * Writes the given <b>$data</b> fragment to the wrapper output stream.
     *
     * @param string $data
     * @return void
     */
    public function write($data)
    {
        fwrite($this->stream, $data);
    }
}
