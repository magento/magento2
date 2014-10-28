<?php
/**
 * The Mail_mimePart class is used to create MIME E-mail messages
 *
 * This class enables you to manipulate and build a mime email
 * from the ground up. The Mail_Mime class is a userfriendly api
 * to this class for people who aren't interested in the internals
 * of mime mail.
 * This class however allows full control over the email.
 *
 * Compatible with PHP versions 4 and 5
 *
 * LICENSE: This LICENSE is in the BSD license style.
 * Copyright (c) 2002-2003, Richard Heyes <richard@phpguru.org>
 * Copyright (c) 2003-2006, PEAR <pear-group@php.net>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met:
 *
 * - Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * - Neither the name of the authors, nor the names of its contributors 
 *   may be used to endorse or promote products derived from this 
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Mail
 * @package    Mail_Mime
 * @author     Richard Heyes  <richard@phpguru.org>
 * @author     Cipriano Groenendal <cipri@php.net>
 * @author     Sean Coates <sean@php.net>
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    CVS: $Id: mimePart.php,v 1.25 2007/05/14 21:43:08 cipri Exp $
 * @link       http://pear.php.net/package/Mail_mime
 */


/**
 * The Mail_mimePart class is used to create MIME E-mail messages
 *
 * This class enables you to manipulate and build a mime email
 * from the ground up. The Mail_Mime class is a userfriendly api
 * to this class for people who aren't interested in the internals
 * of mime mail.
 * This class however allows full control over the email.
 *
 * @category   Mail
 * @package    Mail_Mime
 * @author     Richard Heyes  <richard@phpguru.org>
 * @author     Cipriano Groenendal <cipri@php.net>
 * @author     Sean Coates <sean@php.net>
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Mail_mime
 */
class Mail_mimePart {

   /**
    * The encoding type of this part
    *
    * @var string
    * @access private
    */
    var $_encoding;

   /**
    * An array of subparts
    *
    * @var array
    * @access private
    */
    var $_subparts;

   /**
    * The output of this part after being built
    *
    * @var string
    * @access private
    */
    var $_encoded;

   /**
    * Headers for this part
    *
    * @var array
    * @access private
    */
    var $_headers;

   /**
    * The body of this part (not encoded)
    *
    * @var string
    * @access private
    */
    var $_body;

    /**
     * Constructor.
     *
     * Sets up the object.
     *
     * @param $body   - The body of the mime part if any.
     * @param $params - An associative array of parameters:
     *                  content_type - The content type for this part eg multipart/mixed
     *                  encoding     - The encoding to use, 7bit, 8bit, base64, or quoted-printable
     *                  cid          - Content ID to apply
     *                  disposition  - Content disposition, inline or attachment
     *                  dfilename    - Optional filename parameter for content disposition
     *                  description  - Content description
     *                  charset      - Character set to use
     * @access public
     */
    function Mail_mimePart($body = '', $params = array())
    {
        if (!defined('MAIL_MIMEPART_CRLF')) {
            define('MAIL_MIMEPART_CRLF', defined('MAIL_MIME_CRLF') ? MAIL_MIME_CRLF : "\r\n", TRUE);
        }

        $contentType = array();
        $contentDisp = array();
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'content_type':
                    $contentType['type'] = $value;
                    //$headers['Content-Type'] = $value . (isset($charset) ? '; charset="' . $charset . '"' : '');
                    break;

                case 'encoding':
                    $this->_encoding = $value;
                    $headers['Content-Transfer-Encoding'] = $value;
                    break;

                case 'cid':
                    $headers['Content-ID'] = '<' . $value . '>';
                    break;

                case 'disposition':
                    $contentDisp['disp'] = $value;
                    break;

                case 'dfilename':
                    $contentDisp['filename'] = $value;
                    $contentType['name'] = $value;
                    break;

                case 'description':
                    $headers['Content-Description'] = $value;
                    break;

                case 'charset':
                    $contentType['charset'] = $value;
                    $contentDisp['charset'] = $value;
                    break;

                case 'language':
                    $contentType['language'] = $value;
                    $contentDisp['language'] = $value;
                    break;

                case 'location':
                    $headers['Content-Location'] = $value;
                    break;

            }
        }
        if (isset($contentType['type'])) {
            $headers['Content-Type'] = $contentType['type'];
            if (isset($contentType['name'])) {
                $headers['Content-Type'] .= ';' . MAIL_MIMEPART_CRLF;
                $headers['Content-Type'] .= $this->_buildHeaderParam('name', $contentType['name'], 
                                                isset($contentType['charset']) ? $contentType['charset'] : 'US-ASCII', 
                                                isset($contentType['language']) ? $contentType['language'] : NULL);
            } elseif (isset($contentType['charset'])) {
                $headers['Content-Type'] .= "; charset=\"{$contentType['charset']}\"";
            }
        }


        if (isset($contentDisp['disp'])) {
            $headers['Content-Disposition'] = $contentDisp['disp'];
            if (isset($contentDisp['filename'])) {
                $headers['Content-Disposition'] .= ';' . MAIL_MIMEPART_CRLF;
                $headers['Content-Disposition'] .= $this->_buildHeaderParam('filename', $contentDisp['filename'], 
                                                isset($contentDisp['charset']) ? $contentDisp['charset'] : 'US-ASCII', 
                                                isset($contentDisp['language']) ? $contentDisp['language'] : NULL);
            }
        }
        
        
        
        
        // Default content-type
        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'text/plain';
        }

        //Default encoding
        if (!isset($this->_encoding)) {
            $this->_encoding = '7bit';
        }

        // Assign stuff to member variables
        $this->_encoded  = array();
        $this->_headers  = $headers;
        $this->_body     = $body;
    }

    /**
     * encode()
     *
     * Encodes and returns the email. Also stores
     * it in the encoded member variable
     *
     * @return An associative array containing two elements,
     *         body and headers. The headers element is itself
     *         an indexed array.
     * @access public
     */
    function encode()
    {
        $encoded =& $this->_encoded;

        if (count($this->_subparts)) {
            srand((double)microtime()*1000000);
            $boundary = '=_' . md5(rand() . microtime());
            $this->_headers['Content-Type'] .= ';' . MAIL_MIMEPART_CRLF . "\t" . 'boundary="' . $boundary . '"';

            // Add body parts to $subparts
            for ($i = 0; $i < count($this->_subparts); $i++) {
                $headers = array();
                $tmp = $this->_subparts[$i]->encode();
                foreach ($tmp['headers'] as $key => $value) {
                    $headers[] = $key . ': ' . $value;
                }
                $subparts[] = implode(MAIL_MIMEPART_CRLF, $headers) . MAIL_MIMEPART_CRLF . MAIL_MIMEPART_CRLF . $tmp['body'] . MAIL_MIMEPART_CRLF;
            }

            $encoded['body'] = '--' . $boundary . MAIL_MIMEPART_CRLF . 
                               rtrim(implode('--' . $boundary . MAIL_MIMEPART_CRLF , $subparts), MAIL_MIMEPART_CRLF) . MAIL_MIMEPART_CRLF . 
                               '--' . $boundary.'--' . MAIL_MIMEPART_CRLF;

        } else {
            $encoded['body'] = $this->_getEncodedData($this->_body, $this->_encoding);
        }

        // Add headers to $encoded
        $encoded['headers'] =& $this->_headers;

        return $encoded;
    }

    /**
     * &addSubPart()
     *
     * Adds a subpart to current mime part and returns
     * a reference to it
     *
     * @param $body   The body of the subpart, if any.
     * @param $params The parameters for the subpart, same
     *                as the $params argument for constructor.
     * @return A reference to the part you just added. It is
     *         crucial if using multipart/* in your subparts that
     *         you use =& in your script when calling this function,
     *         otherwise you will not be able to add further subparts.
     * @access public
     */
    function &addSubPart($body, $params)
    {
        $this->_subparts[] = new Mail_mimePart($body, $params);
        return $this->_subparts[count($this->_subparts) - 1];
    }

    /**
     * _getEncodedData()
     *
     * Returns encoded data based upon encoding passed to it
     *
     * @param $data     The data to encode.
     * @param $encoding The encoding type to use, 7bit, base64,
     *                  or quoted-printable.
     * @access private
     */
    function _getEncodedData($data, $encoding)
    {
        switch ($encoding) {
            case '8bit':
            case '7bit':
                return $data;
                break;

            case 'quoted-printable':
                return $this->_quotedPrintableEncode($data);
                break;

            case 'base64':
                return rtrim(chunk_split(base64_encode($data), 76, MAIL_MIMEPART_CRLF));
                break;

            default:
                return $data;
        }
    }

    /**
     * quotedPrintableEncode()
     *
     * Encodes data to quoted-printable standard.
     *
     * @param $input    The data to encode
     * @param $line_max Optional max line length. Should
     *                  not be more than 76 chars
     *
     * @access private
     */
    function _quotedPrintableEncode($input , $line_max = 76)
    {
        $lines  = preg_split("/\r?\n/", $input);
        $eol    = MAIL_MIMEPART_CRLF;
        $escape = '=';
        $output = '';

        while (list(, $line) = each($lines)) {

            $line    = preg_split('||', $line, -1, PREG_SPLIT_NO_EMPTY);
            $linlen     = count($line);
            $newline = '';

            for ($i = 0; $i < $linlen; $i++) {
                $char = $line[$i];
                $dec  = ord($char);

                if (($dec == 32) AND ($i == ($linlen - 1))) {    // convert space at eol only
                    $char = '=20';

                } elseif (($dec == 9) AND ($i == ($linlen - 1))) {  // convert tab at eol only
                    $char = '=09';
                } elseif ($dec == 9) {
                    ; // Do nothing if a tab.
                } elseif (($dec == 61) OR ($dec < 32 ) OR ($dec > 126)) {
                    $char = $escape . strtoupper(sprintf('%02s', dechex($dec)));
                } elseif (($dec == 46) AND ($newline == '')) {
                    //Bug #9722: convert full-stop at bol
                    //Some Windows servers need this, won't break anything (cipri)
                    $char = '=2E';
                }

                if ((strlen($newline) + strlen($char)) >= $line_max) {        // MAIL_MIMEPART_CRLF is not counted
                    $output  .= $newline . $escape . $eol;                    // soft line break; " =\r\n" is okay
                    $newline  = '';
                }
                $newline .= $char;
            } // end of for
            $output .= $newline . $eol;
        }
        $output = substr($output, 0, -1 * strlen($eol)); // Don't want last crlf
        return $output;
    }

    /**
     * _buildHeaderParam()
     *
     * Encodes the paramater of a header.
     *
     * @param $name         The name of the header-parameter
     * @param $value        The value of the paramter
     * @param $charset      The characterset of $value
     * @param $language     The language used in $value
     * @param $maxLength    The maximum length of a line. Defauls to 75
     *
     * @access private
     */
    function _buildHeaderParam($name, $value, $charset=NULL, $language=NULL, $maxLength=75)
    {
        //If we find chars to encode, or if charset or language
        //is not any of the defaults, we need to encode the value.
        $shouldEncode = 0;
        $secondAsterisk = '';
        if (preg_match('#([\x80-\xFF]){1}#', $value)) {
            $shouldEncode = 1;
        } elseif ($charset && (strtolower($charset) != 'us-ascii')) {
            $shouldEncode = 1;
        } elseif ($language && ($language != 'en' && $language != 'en-us')) {
            $shouldEncode = 1;
        }
        if ($shouldEncode) {
            $search  = array('%',   ' ',   "\t");
            $replace = array('%25', '%20', '%09');
            $encValue = str_replace($search, $replace, $value);
            $encValue = preg_replace('#([\x80-\xFF])#e', '"%" . strtoupper(dechex(ord("\1")))', $encValue);
            $value = "$charset'$language'$encValue";
            $secondAsterisk = '*';
        }
        $header = " {$name}{$secondAsterisk}=\"{$value}\"; ";
        if (strlen($header) <= $maxLength) {
            return $header;
        }

        $preLength = strlen(" {$name}*0{$secondAsterisk}=\"");
        $sufLength = strlen("\";");
        $maxLength = MAX(16, $maxLength - $preLength - $sufLength - 2);
        $maxLengthReg = "|(.{0,$maxLength}[^\%][^\%])|";

        $headers = array();
        $headCount = 0;
        while ($value) {
            $matches = array();
            $found = preg_match($maxLengthReg, $value, $matches);
            if ($found) {
                $headers[] = " {$name}*{$headCount}{$secondAsterisk}=\"{$matches[0]}\"";
                $value = substr($value, strlen($matches[0]));
            } else {
                $headers[] = " {$name}*{$headCount}{$secondAsterisk}=\"{$value}\"";
                $value = "";
            }
            $headCount++;
        }
        $headers = implode(MAIL_MIMEPART_CRLF, $headers) . ';';
        return $headers;
    }
} // End of class
