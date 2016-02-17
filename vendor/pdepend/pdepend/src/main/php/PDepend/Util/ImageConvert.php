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

namespace PDepend\Util;

/**
 * Simple utility class that is used to create different image formats. This
 * class can use the ImageMagick cli tool <b>convert</b> and the pecl extension
 * <b>pecl/imagick</b>.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class ImageConvert
{
    /**
     * Tries to converts the <b>$input</b> image into the <b>$output</b> format.
     *
     * @param  string $input  The input file.
     * @param  string $output The output file.
     * @return void
     */
    public static function convert($input, $output)
    {
        $inputType  = strtolower(pathinfo($input, PATHINFO_EXTENSION));
        $outputType = strtolower(pathinfo($output, PATHINFO_EXTENSION));

        // Check for output file without extension and reuse input type
        if ($outputType === '') {
            $outputType = $inputType;
            $output    .= ".{$outputType}";
        }

        if ($inputType === 'svg') {
            self::prepareSvg($input);
        }

        if ($inputType === $outputType) {
            file_put_contents($output, file_get_contents($input));
        } elseif (extension_loaded('imagick') === true) {
            $imagick = new \Imagick($input);
            $imagick->setImageFormat($outputType);
            $imagick->writeImage($output);

            // The following code is not testable when imagick is installed
            // @codeCoverageIgnoreStart
        } elseif (self::hasImagickConvert() === true) {
            $input  = escapeshellarg($input);
            $output = escapeshellarg($output);

            system("convert {$input} {$output}");
        } else {
            $fallback = substr($output, 0, -strlen($outputType)) . $inputType;

            echo "WARNING: Cannot generate image of type '{$outputType}'. This",
                 " feature needs either the\n         pecl/imagick extension or",
                 " the ImageMagick cli tool 'convert'.\n\n",
                 "Writing alternative image:\n{$fallback}\n\n";

            file_put_contents($fallback, file_get_contents($input));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Tests that the ImageMagick CLI tool <b>convert</b> exists.
     *
     * @return boolean
     */
    protected static function hasImagickConvert()
    {
        // @codeCoverageIgnoreStart
        $desc = array(
            0  =>  array('pipe', 'r'),
            1  =>  array('pipe', 'w'),
            2  =>  array('pipe', 'a'),
        );

        $proc = proc_open('convert', $desc, $pipes);
        if (is_resource($proc)) {
            fwrite($pipes[0], '-version');
            fclose($pipes[0]);

            return (0 === proc_close($proc));
        }
        return false;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Utility method for svg input files.
     *
     * If the input file has the mime type svg and a configuration file with
     * imageConvert options exists, this method will prepare the input image
     * file.
     *
     * @param  string $input The input svg file.
     * @return void
     */
    protected static function prepareSvg($input)
    {
        // Check for a configuration instance
        if (($config = ConfigurationInstance::get()) === null) {
            return;
        }

        $svg = file_get_contents($input);

        // Check for font family
        if (isset($config->imageConvert->fontFamily)) {
            // Get font family
            $fontFamily = (string) $config->imageConvert->fontFamily;
            // Replace CSS separators
            $fontReplace = 'font-family:' . strtr($fontFamily, ';:', '  ');
            $fontPattern = '/font-family:\s*Arial/';

            $svg = preg_replace($fontPattern, $fontReplace, $svg);
        }

        // Check for font size
        if (isset($config->imageConvert->fontSize)) {
            // Get font size
            $fontSize = abs((float) $config->imageConvert->fontSize);

            // Fetch all font-size expressions
            preg_match_all('/font-size:\s*(\d+)/', $svg, $fontSizes);
            $fontSizes = array_unique($fontSizes[1]);

            $resize = ($fontSize - max($fontSizes));
            foreach ($fontSizes as $fontSize) {
                // Calculate resize value
                $fontReplace = 'font-size:' . ($fontSize + $resize);
                $fontPattern = "/font-size:\s*{$fontSize}/";

                $svg = preg_replace($fontPattern, $fontReplace, $svg);
            }
        }

        file_put_contents($input, $svg);
    }
}
