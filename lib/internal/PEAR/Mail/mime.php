<?php
/**
 * The Mail_Mime class is used to create MIME E-mail messages
 *
 * The Mail_Mime class provides an OO interface to create MIME
 * enabled email messages. This way you can create emails that
 * contain plain-text bodies, HTML bodies, attachments, inline
 * images and specific headers.
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
 * @category  Mail
 * @package   Mail_Mime
 * @author    Richard Heyes  <richard@phpguru.org>
 * @author    Tomas V.V. Cox <cox@idecnet.com>
 * @author    Cipriano Groenendal <cipri@php.net>
 * @author    Sean Coates <sean@php.net>
 * @copyright 2003-2006 PEAR <pear-group@php.net>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   CVS: $Id: mime.php,v 1.81 2007/06/21 19:08:28 cipri Exp $
 * @link      http://pear.php.net/package/Mail_mime
 *
 *            This class is based on HTML Mime Mail class from
 *            Richard Heyes <richard@phpguru.org> which was based also
 *            in the mime_mail.class by Tobias Ratschiller <tobias@dnet.it>
 *            and Sascha Schumann <sascha@schumann.cx>
 */


/**
 * require PEAR
 *
 * This package depends on PEAR to raise errors.
 */
require_once 'PEAR.php';

/**
 * require Mail_mimePart
 *
 * Mail_mimePart contains the code required to
 * create all the different parts a mail can
 * consist of.
 */
require_once 'Mail/mimePart.php';


/**
 * The Mail_Mime class provides an OO interface to create MIME
 * enabled email messages. This way you can create emails that
 * contain plain-text bodies, HTML bodies, attachments, inline
 * images and specific headers.
 *
 * @category  Mail
 * @package   Mail_Mime
 * @author    Richard Heyes  <richard@phpguru.org>
 * @author    Tomas V.V. Cox <cox@idecnet.com>
 * @author    Cipriano Groenendal <cipri@php.net>
 * @author    Sean Coates <sean@php.net>
 * @copyright 2003-2006 PEAR <pear-group@php.net>
 * @license   http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/Mail_mime
 */
class Mail_mime
{
    /**
     * Contains the plain text part of the email
     *
     * @var string
     * @access private
     */
    var $_txtbody;

    /**
     * Contains the html part of the email
     *
     * @var string
     * @access private
     */
    var $_htmlbody;

    /**
     * contains the mime encoded text
     *
     * @var string
     * @access private
     */
    var $_mime;

    /**
     * contains the multipart content
     *
     * @var string
     * @access private
     */
    var $_multipart;

    /**
     * list of the attached images
     *
     * @var array
     * @access private
     */
    var $_html_images = array();

    /**
     * list of the attachements
     *
     * @var array
     * @access private
     */
    var $_parts = array();

    /**
     * Build parameters
     *
     * @var array
     * @access private
     */
    var $_build_params = array();

    /**
     * Headers for the mail
     *
     * @var array
     * @access private
     */
    var $_headers = array();

    /**
     * End Of Line sequence (for serialize)
     *
     * @var string
     * @access private
     */
    var $_eol;


    /**
     * Constructor function.
     *
     * @param string $crlf what type of linebreak to use.
     *                     Defaults to "\r\n"
     *
     * @return void
     *
     * @access public
     */
    function Mail_mime($crlf = "\r\n")
    {
        $this->_setEOL($crlf);
        $this->_build_params = array(
                                     'head_encoding' => 'quoted-printable',
                                     'text_encoding' => '7bit',
                                     'html_encoding' => 'quoted-printable',
                                     '7bit_wrap'     => 998,
                                     'html_charset'  => 'ISO-8859-1',
                                     'text_charset'  => 'ISO-8859-1',
                                     'head_charset'  => 'ISO-8859-1'
                                    );
    }

    /**
     * wakeup function called by unserialize. It re-sets the EOL constant
     *
     * @access private
     * @return void
     */
    function __wakeup()
    {
        $this->_setEOL($this->_eol);
    }


    /**
     * Accessor function to set the body text. Body text is used if
     * it's not an html mail being sent or else is used to fill the
     * text/plain part that emails clients who don't support
     * html should show.
     *
     * @param string $data   Either a string or
     *                        the file name with the contents
     * @param bool   $isfile If true the first param should be treated
     *                        as a file name, else as a string (default)
     * @param bool   $append If true the text or file is appended to
     *                        the existing body, else the old body is
     *                        overwritten
     *
     * @return mixed   true on success or PEAR_Error object
     * @access public
     */
    function setTXTBody($data, $isfile = false, $append = false)
    {
        if (!$isfile) {
            if (!$append) {
                $this->_txtbody = $data;
            } else {
                $this->_txtbody .= $data;
            }
        } else {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            if (!$append) {
                $this->_txtbody = $cont;
            } else {
                $this->_txtbody .= $cont;
            }
        }
        return true;
    }

    /**
     * Adds a html part to the mail.
     *
     * @param string $data   either a string or the file name with the
     *                        contents
     * @param bool   $isfile a flag that determines whether $data is a
     *                        filename, or a string(false, default)
     *
     * @return bool    true on success
     * @access public
     */
    function setHTMLBody($data, $isfile = false)
    {
        if (!$isfile) {
            $this->_htmlbody = $data;
        } else {
            $cont = $this->_file2str($data);
            if (PEAR::isError($cont)) {
                return $cont;
            }
            $this->_htmlbody = $cont;
        }

        return true;
    }

    /**
     * Adds an image to the list of embedded images.
     *
     * @param string $file   the image file name OR image data itself
     * @param string $c_type the content type
     * @param string $name   the filename of the image.
     *                        Only used if $file is the image data.
     * @param bool   $isfile whether $file is a filename or not.
     *                        Defaults to true
     *
     * @return bool          true on success
     * @access public
     */
    function addHTMLImage($file, $c_type='application/octet-stream',
                          $name = '', $isfile = true)
    {
        $filedata = ($isfile === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfile === true) {
            $filename = ($name == '' ? $file : $name);
        } else {
            $filename = $name;
        }
        if (PEAR::isError($filedata)) {
            return $filedata;
        }
        $this->_html_images[] = array(
                                      'body'   => $filedata,
                                      'name'   => $filename,
                                      'c_type' => $c_type,
                                      'cid'    => md5(uniqid(time()))
                                     );
        return true;
    }

    /**
     * Adds a file to the list of attachments.
     *
     * @param string $file        The file name of the file to attach
     *                             OR the file contents itself
     * @param string $c_type      The content type
     * @param string $name        The filename of the attachment
     *                             Only use if $file is the contents
     * @param bool   $isfile      Whether $file is a filename or not
     *                             Defaults to true
     * @param string $encoding    The type of encoding to use.
     *                             Defaults to base64.
     *                             Possible values: 7bit, 8bit, base64, 
     *                             or quoted-printable.
     * @param string $disposition The content-disposition of this file
     *                             Defaults to attachment.
     *                             Possible values: attachment, inline.
     * @param string $charset     The character set used in the filename
     *                             of this attachment.
     * @param string $language    The language of the attachment
     * @param string $location    The RFC 2557.4 location of the attachment
     *
     * @return mixed true on success or PEAR_Error object
     * @access public
     */
    function addAttachment($file,
                           $c_type      = 'application/octet-stream',
                           $name        = '',
                            $isfile     = true,
                           $encoding    = 'base64',
                           $disposition = 'attachment',
                           $charset     = '',
                            $language   = '',
                           $location    = '')
    {
        $filedata = ($isfile === true) ? $this->_file2str($file)
                                           : $file;
        if ($isfile === true) {
            // Force the name the user supplied, otherwise use $file
            $filename = (strlen($name)) ? $name : $file;
        } else {
            $filename = $name;
        }
        if (!strlen($filename)) {
            $msg = "The supplied filename for the attachment can't be empty";
            $err = PEAR::raiseError($msg);
            return $err;
        }
        $filename = basename($filename);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $this->_parts[] = array(
                                'body'        => $filedata,
                                'name'        => $filename,
                                'c_type'      => $c_type,
                                'encoding'    => $encoding,
                                'charset'     => $charset,
                                'language'    => $language,
                                'location'    => $location,
                                'disposition' => $disposition
                               );
        return true;
    }

    /**
     * Get the contents of the given file name as string
     *
     * @param string $file_name path of file to process
     *
     * @return string  contents of $file_name
     * @access private
     */
    function &_file2str($file_name)
    {
        if (!is_readable($file_name)) {
            $err = PEAR::raiseError('File is not readable ' . $file_name);
            return $err;
        }
        if (!$fd = fopen($file_name, 'rb')) {
            $err = PEAR::raiseError('Could not open ' . $file_name);
            return $err;
        }
        $filesize = filesize($file_name);
        if ($filesize == 0) {
            $cont =  "";
        } else {
            if ($magic_quote_setting = get_magic_quotes_runtime()) {
                set_magic_quotes_runtime(0);
            }
            $cont = fread($fd, $filesize);
            if ($magic_quote_setting) {
                set_magic_quotes_runtime($magic_quote_setting);
            }
        }
        fclose($fd);
        return $cont;
    }

    /**
     * Adds a text subpart to the mimePart object and
     * returns it during the build process.
     *
     * @param mixed  &$obj The object to add the part to, or
     *                      null if a new object is to be created.
     * @param string $text The text to add.
     *
     * @return object  The text mimePart object
     * @access private
     */
    function &_addTextPart(&$obj, $text)
    {
        $params['content_type'] = 'text/plain';
        $params['encoding']     = $this->_build_params['text_encoding'];
        $params['charset']      = $this->_build_params['text_charset'];
        if (is_object($obj)) {
            $ret = $obj->addSubpart($text, $params);
            return $ret;
        } else {
            $ret = new Mail_mimePart($text, $params);
            return $ret;
        }
    }

    /**
     * Adds a html subpart to the mimePart object and
     * returns it during the build process.
     *
     * @param mixed &$obj The object to add the part to, or
     *                     null if a new object is to be created.
     *
     * @return object The html mimePart object
     * @access private
     */
    function &_addHtmlPart(&$obj)
    {
        $params['content_type'] = 'text/html';
        $params['encoding']     = $this->_build_params['html_encoding'];
        $params['charset']      = $this->_build_params['html_charset'];
        if (is_object($obj)) {
            $ret = $obj->addSubpart($this->_htmlbody, $params);
            return $ret;
        } else {
            $ret = new Mail_mimePart($this->_htmlbody, $params);
            return $ret;
        }
    }

    /**
     * Creates a new mimePart object, using multipart/mixed as
     * the initial content-type and returns it during the
     * build process.
     *
     * @return object The multipart/mixed mimePart object
     * @access private
     */
    function &_addMixedPart()
    {
        $params                 = array();
        $params['content_type'] = 'multipart/mixed';
        
        //Create empty multipart/mixed Mail_mimePart object to return
        $ret = new Mail_mimePart('', $params);
        return $ret;
    }

    /**
     * Adds a multipart/alternative part to a mimePart
     * object (or creates one), and returns it during
     * the build process.
     *
     * @param mixed &$obj The object to add the part to, or
     *                     null if a new object is to be created.
     *
     * @return object  The multipart/mixed mimePart object
     * @access private
     */
    function &_addAlternativePart(&$obj)
    {
        $params['content_type'] = 'multipart/alternative';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            $ret = new Mail_mimePart('', $params);
            return $ret;
        }
    }

    /**
     * Adds a multipart/related part to a mimePart
     * object (or creates one), and returns it during
     * the build process.
     *
     * @param mixed &$obj The object to add the part to, or
     *                     null if a new object is to be created
     *
     * @return object  The multipart/mixed mimePart object
     * @access private
     */
    function &_addRelatedPart(&$obj)
    {
        $params['content_type'] = 'multipart/related';
        if (is_object($obj)) {
            return $obj->addSubpart('', $params);
        } else {
            $ret = new Mail_mimePart('', $params);
            return $ret;
        }
    }

    /**
     * Adds an html image subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param object &$obj  The mimePart to add the image to
     * @param array  $value The image information
     *
     * @return object  The image mimePart object
     * @access private
     */
    function &_addHtmlImagePart(&$obj, $value)
    {
        $params['content_type'] = $value['c_type'];
        $params['encoding']     = 'base64';
        $params['disposition']  = 'inline';
        $params['dfilename']    = $value['name'];
        $params['cid']          = $value['cid'];
        
        $ret = $obj->addSubpart($value['body'], $params);
        return $ret;
    
    }

    /**
     * Adds an attachment subpart to a mimePart object
     * and returns it during the build process.
     *
     * @param object &$obj  The mimePart to add the image to
     * @param array  $value The attachment information
     *
     * @return object  The image mimePart object
     * @access private
     */
    function &_addAttachmentPart(&$obj, $value)
    {
        $params['dfilename'] = $value['name'];
        $params['encoding']  = $value['encoding'];
        if ($value['charset']) {
            $params['charset'] = $value['charset'];
        }
        if ($value['language']) {
            $params['language'] = $value['language'];
        }
        if ($value['location']) {
            $params['location'] = $value['location'];
        }
        $params['content_type'] = $value['c_type'];
        $params['disposition']  = isset($value['disposition']) ? 
                                  $value['disposition'] : 'attachment';
        $ret = $obj->addSubpart($value['body'], $params);
        return $ret;
    }

    /**
     * Returns the complete e-mail, ready to send using an alternative
     * mail delivery method. Note that only the mailpart that is made
     * with Mail_Mime is created. This means that,
     * YOU WILL HAVE NO TO: HEADERS UNLESS YOU SET IT YOURSELF 
     * using the $xtra_headers parameter!
     * 
     * @param string $separation   The separation etween these two parts.
     * @param array  $build_params The Build parameters passed to the
     *                             &get() function. See &get for more info.
     * @param array  $xtra_headers The extra headers that should be passed
     *                             to the &headers() function.
     *                             See that function for more info.
     * @param bool   $overwrite    Overwrite the existing headers with new.
     *
     * @return string The complete e-mail.
     * @access public
     */
    function getMessage(
                        $separation   = null, 
                        $build_params = null, 
                        $xtra_headers = null, 
                        $overwrite    = false
                       )
    {
        if ($separation === null) {
            $separation = MAIL_MIME_CRLF;
        }
        $body = $this->get($build_params);
        $head = $this->txtHeaders($xtra_headers, $overwrite);
        $mail = $head . $separation . $body;
        return $mail;
    }


    /**
     * Builds the multipart message from the list ($this->_parts) and
     * returns the mime content.
     *
     * @param array $build_params Build parameters that change the way the email
     *                             is built. Should be associative. Can contain:
     *                head_encoding  -  What encoding to use for the headers. 
     *                                  Options: quoted-printable or base64
     *                                  Default is quoted-printable
     *                text_encoding  -  What encoding to use for plain text
     *                                  Options: 7bit, 8bit,
     *                                  base64, or quoted-printable
     *                                  Default is 7bit
     *                html_encoding  -  What encoding to use for html
     *                                  Options: 7bit, 8bit,
     *                                  base64, or quoted-printable
     *                                  Default is quoted-printable
     *                7bit_wrap      -  Number of characters before text is
     *                                  wrapped in 7bit encoding
     *                                  Default is 998
     *                html_charset   -  The character set to use for html.
     *                                  Default is iso-8859-1
     *                text_charset   -  The character set to use for text.
     *                                  Default is iso-8859-1
     *                head_charset   -  The character set to use for headers.
     *                                  Default is iso-8859-1
     *
     * @return string The mime content
     * @access public
     */
    function &get($build_params = null)
    {
        if (isset($build_params)) {
            while (list($key, $value) = each($build_params)) {
                $this->_build_params[$key] = $value;
            }
        }
        
        if (isset($this->_headers['From'])){
            $domain = @strstr($this->_headers['From'],'@');
            //Bug #11381: Illegal characters in domain ID
            $domain = str_replace(array("<", ">", "&", "(", ")", " ", "\"", "'"), "", $domain);
            $domain = urlencode($domain);
            foreach($this->_html_images as $i => $img){
                $this->_html_images[$i]['cid'] = $this->_html_images[$i]['cid'] . $domain;
            }
        }
        
        if (count($this->_html_images) AND isset($this->_htmlbody)) {
            foreach ($this->_html_images as $key => $value) {
                $regex   = array();
                $regex[] = '#(\s)((?i)src|background|href(?-i))\s*=\s*(["\']?)' .
                            preg_quote($value['name'], '#') . '\3#';
                $regex[] = '#(?i)url(?-i)\(\s*(["\']?)' .
                            preg_quote($value['name'], '#') . '\1\s*\)#';

                $rep   = array();
                $rep[] = '\1\2=\3cid:' . $value['cid'] .'\3';
                $rep[] = 'url(\1cid:' . $value['cid'] . '\2)';

                $this->_htmlbody = preg_replace($regex, $rep, $this->_htmlbody);
                $this->_html_images[$key]['name'] = 
                    basename($this->_html_images[$key]['name']);
            }
        }

        $null        = null;
        $attachments = count($this->_parts)                 ? true : false;
        $html_images = count($this->_html_images)           ? true : false;
        $html        = strlen($this->_htmlbody)             ? true : false;
        $text        = (!$html AND strlen($this->_txtbody)) ? true : false;

        switch (true) {
        case $text AND !$attachments:
            $message =& $this->_addTextPart($null, $this->_txtbody);
            break;

        case !$text AND !$html AND $attachments:
            $message =& $this->_addMixedPart();
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $text AND $attachments:
            $message =& $this->_addMixedPart();
            $this->_addTextPart($message, $this->_txtbody);
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $html AND !$attachments AND !$html_images:
            if (isset($this->_txtbody)) {
                $message =& $this->_addAlternativePart($null);
                $this->_addTextPart($message, $this->_txtbody);
                $this->_addHtmlPart($message);
            } else {
                $message =& $this->_addHtmlPart($null);
            }
            break;

        case $html AND !$attachments AND $html_images:
            $message =& $this->_addRelatedPart($null);
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $this->_addHtmlPart($alt);
            } else {
                $this->_addHtmlPart($message);
            }
            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($message, $this->_html_images[$i]);
            }
            break;

        case $html AND $attachments AND !$html_images:
            $message =& $this->_addMixedPart();
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $this->_addHtmlPart($alt);
            } else {
                $this->_addHtmlPart($message);
            }
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        case $html AND $attachments AND $html_images:
            $message =& $this->_addMixedPart();
            if (isset($this->_txtbody)) {
                $alt =& $this->_addAlternativePart($message);
                $this->_addTextPart($alt, $this->_txtbody);
                $rel =& $this->_addRelatedPart($alt);
            } else {
                $rel =& $this->_addRelatedPart($message);
            }
            $this->_addHtmlPart($rel);
            for ($i = 0; $i < count($this->_html_images); $i++) {
                $this->_addHtmlImagePart($rel, $this->_html_images[$i]);
            }
            for ($i = 0; $i < count($this->_parts); $i++) {
                $this->_addAttachmentPart($message, $this->_parts[$i]);
            }
            break;

        }

        if (isset($message)) {
            $output = $message->encode();
            
            $this->_headers = array_merge($this->_headers,
                                          $output['headers']);
            $body = $output['body'];
            return $body;

        } else {
            $ret = false;
            return $ret;
        }
    }

    /**
     * Returns an array with the headers needed to prepend to the email
     * (MIME-Version and Content-Type). Format of argument is:
     * $array['header-name'] = 'header-value';
     *
     * @param array $xtra_headers Assoc array with any extra headers.
     *                             Optional.
     * @param bool  $overwrite    Overwrite already existing headers.
     * 
     * @return array Assoc array with the mime headers
     * @access public
     */
    function &headers($xtra_headers = null, $overwrite = false)
    {
        // Content-Type header should already be present,
        // So just add mime version header
        $headers['MIME-Version'] = '1.0';
        if (isset($xtra_headers)) {
            $headers = array_merge($headers, $xtra_headers);
        }
        if ($overwrite) {
            $this->_headers = array_merge($this->_headers, $headers);
        } else {
            $this->_headers = array_merge($headers, $this->_headers);
        }

        $encodedHeaders = $this->_encodeHeaders($this->_headers);
        return $encodedHeaders;
    }

    /**
     * Get the text version of the headers
     * (usefull if you want to use the PHP mail() function)
     *
     * @param array $xtra_headers Assoc array with any extra headers.
     *                             Optional.
     * @param bool  $overwrite    Overwrite the existing heaers with new.
     *
     * @return string  Plain text headers
     * @access public
     */
    function txtHeaders($xtra_headers = null, $overwrite = false)
    {
        $headers = $this->headers($xtra_headers, $overwrite);
        
        $ret = '';
        foreach ($headers as $key => $val) {
            $ret .= "$key: $val" . MAIL_MIME_CRLF;
        }
        return $ret;
    }

    /**
     * Sets the Subject header
     *
     * @param string $subject String to set the subject to.
     *
     * @return void
     * @access public
     */
    function setSubject($subject)
    {
        $this->_headers['Subject'] = $subject;
    }

    /**
     * Set an email to the From (the sender) header
     *
     * @param string $email The email address to use
     *
     * @return void
     * @access public
     */
    function setFrom($email)
    {
        $this->_headers['From'] = $email;
    }

    /**
     * Add an email to the Cc (carbon copy) header
     * (multiple calls to this method are allowed)
     *
     * @param string $email The email direction to add
     *
     * @return void
     * @access public
     */
    function addCc($email)
    {
        if (isset($this->_headers['Cc'])) {
            $this->_headers['Cc'] .= ", $email";
        } else {
            $this->_headers['Cc'] = $email;
        }
    }

    /**
     * Add an email to the Bcc (blank carbon copy) header
     * (multiple calls to this method are allowed)
     *
     * @param string $email The email direction to add
     *
     * @return void
     * @access public
     */
    function addBcc($email)
    {
        if (isset($this->_headers['Bcc'])) {
            $this->_headers['Bcc'] .= ", $email";
        } else {
            $this->_headers['Bcc'] = $email;
        }
    }

    /**
     * Since the PHP send function requires you to specifiy 
     * recipients (To: header) separately from the other
     * headers, the To: header is not properly encoded.
     * To fix this, you can use this public method to 
     * encode your recipients before sending to the send
     * function
     *
     * @param string $recipients A comma-delimited list of recipients
     *
     * @return string Encoded data
     * @access public
     */
    function encodeRecipients($recipients)
    {
        $input = array("To" => $recipients);
        $retval = $this->_encodeHeaders($input);
        return $retval["To"] ;
    }

    /**
     * Encodes a header as per RFC2047
     *
     * @param array $input  The header data to encode
     * @param array $params Extra build parameters
     *
     * @return array Encoded data
     * @access private
     */
    function _encodeHeaders($input, $params = array())
    {
        
        $build_params = $this->_build_params;
        while (list($key, $value) = each($params)) {
            $build_params[$key] = $value;
        }
        //$hdr_name: Name of the heaer
        //$hdr_value: Full line of header value.
        //$hdr_value_out: The recombined $hdr_val-atoms, or the encoded string.
                
        $useIconv = true;        
        if (isset($build_params['ignore-iconv'])) {
            $useIconv = !$build_params['ignore-iconv'];
        }            
        foreach ($input as $hdr_name => $hdr_value) {
            if (preg_match('#([\x80-\xFF]){1}#', $hdr_value)) {
                if (function_exists('iconv_mime_encode') && $useIconv) {
                    $imePrefs = array();
                    if ($build_params['head_encoding'] == 'base64') {
                        $imePrefs['scheme'] = 'B';
                    } else {
                        $imePrefs['scheme'] = 'Q';
                    }
                    $imePrefs['input-charset']  = $build_params['head_charset'];
                    $imePrefs['output-charset'] = $build_params['head_charset'];
                    $imePrefs['line-length'] = 74;
                    $imePrefs['line-break-chars'] = "\r\n"; //Specified in RFC2047
                    
                    $hdr_value = iconv_mime_encode($hdr_name, $hdr_value, $imePrefs);
                    $hdr_value = preg_replace("#^{$hdr_name}\:\ #", "", $hdr_value);
                } elseif ($build_params['head_encoding'] == 'base64') {
                    //Base64 encoding has been selected.
                    //Base64 encode the entire string
                    $hdr_value = base64_encode($hdr_value);
                    
                    //Generate the header using the specified params and dynamicly 
                    //determine the maximum length of such strings.
                    //75 is the value specified in the RFC. The first -2 is there so 
                    //the later regexp doesn't break any of the translated chars.
                    //The -2 on the first line-regexp is to compensate for the ": "
                    //between the header-name and the header value
                    $prefix = '=?' . $build_params['head_charset'] . '?B?';
                    $suffix = '?=';
                    $maxLength = 75 - strlen($prefix . $suffix) - 2;
                    $maxLength1stLine = $maxLength - strlen($hdr_name) - 2;

                    //We can cut base4 every 4 characters, so the real max
                    //we can get must be rounded down.
                    $maxLength = $maxLength - ($maxLength % 4);
                    $maxLength1stLine = $maxLength1stLine - ($maxLength1stLine % 4);
                    
                    $cutpoint = $maxLength1stLine;
                    $hdr_value_out = $hdr_value;
                    $output = "";
                    while ($hdr_value_out) {
                        //Split translated string at every $maxLength
                        $part = substr($hdr_value_out, 0, $cutpoint);
                        $hdr_value_out = substr($hdr_value_out, $cutpoint);
                        $cutpoint = $maxLength;
                        //RFC 2047 specifies that any split header should 
                        //be seperated by a CRLF SPACE. 
                        if ($output) {
                            $output .=  "\r\n ";
                        }
                        $output .= $prefix . $part . $suffix;
                    }
                    $hdr_value = $output;
                } else {
                    //quoted-printable encoding has been selected

                    //Fix for Bug #10298, Ota Mares <om@viazenetti.de>
                    //Check if there is a double quote at beginning or end of
                    //the string to prevent that an open or closing quote gets 
                    //ignored because it is encapsuled by an encoding pre/suffix.
                    //Remove the double quote and set the specific prefix or 
                    //suffix variable so that we can concat the encoded string and
                    //the double quotes back together to get the intended string.
                    $quotePrefix = $quoteSuffix = '';
                    if ($hdr_value{0} == '"') {
                        $hdr_value = substr($hdr_value, 1);
                        $quotePrefix = '"';
                    }
                    if ($hdr_value{strlen($hdr_value)-1} == '"') {
                        $hdr_value = substr($hdr_value, 0, -1);
                        $quoteSuffix = '"';
                    }
                    
                    //Generate the header using the specified params and dynamicly 
                    //determine the maximum length of such strings.
                    //75 is the value specified in the RFC. The -2 is there so 
                    //the later regexp doesn't break any of the translated chars.
                    //The -2 on the first line-regexp is to compensate for the ": "
                    //between the header-name and the header value
                    $prefix = '=?' . $build_params['head_charset'] . '?Q?';
                    $suffix = '?=';
                    $maxLength = 75 - strlen($prefix . $suffix) - 2 - 1;
                    $maxLength1stLine = $maxLength - strlen($hdr_name) - 2;
                    $maxLength = $maxLength - 1;
                    
                    //Replace all special characters used by the encoder.
                    $search  = array('=',   '_',   '?',   ' ');
                    $replace = array('=3D', '=5F', '=3F', '_');
                    $hdr_value = str_replace($search, $replace, $hdr_value);
                    
                    //Replace all extended characters (\x80-xFF) with their
                    //ASCII values.
                    $hdr_value = preg_replace('#([\x80-\xFF])#e',
                        '"=" . strtoupper(dechex(ord("\1")))',
                        $hdr_value);

                    //This regexp will break QP-encoded text at every $maxLength
                    //but will not break any encoded letters.
                    $reg1st = "|(.{0,$maxLength1stLine}[^\=][^\=])|";
                    $reg2nd = "|(.{0,$maxLength}[^\=][^\=])|";
                    //Fix for Bug #10298, Ota Mares <om@viazenetti.de>
                    //Concat the double quotes and encoded string together
                    $hdr_value = $quotePrefix . $hdr_value . $quoteSuffix;
                    

                    $hdr_value_out = $hdr_value;
                    $realMax = $maxLength1stLine + strlen($prefix . $suffix);
                    if (strlen($hdr_value_out) >= $realMax) {
                        //Begin with the regexp for the first line.
                        $reg = $reg1st;
                        $output = "";
                        while ($hdr_value_out) {
                            //Split translated string at every $maxLength
                            //But make sure not to break any translated chars.
                            $found = preg_match($reg, $hdr_value_out, $matches);
                            
                            //After this first line, we need to use a different
                            //regexp for the first line.
                            $reg = $reg2nd;
                            
                            //Save the found part and encapsulate it in the
                            //prefix & suffix. Then remove the part from the
                            //$hdr_value_out variable.
                            if ($found) {
                                $part = $matches[0];
                                $len = strlen($matches[0]);
                                $hdr_value_out = substr($hdr_value_out, $len);
                            } else {
                                $part = $hdr_value_out;
                                $hdr_value_out = "";
                            }
                            
                            //RFC 2047 specifies that any split header should 
                            //be seperated by a CRLF SPACE
                            if ($output) {
                                $output .=  "\r\n ";
                            }
                            $output .= $prefix . $part . $suffix;
                        }
                        $hdr_value_out = $output;
                    } else {
                        $hdr_value_out = $prefix . $hdr_value_out . $suffix;
                    }
                    $hdr_value = $hdr_value_out;
                }
            }
            $input[$hdr_name] = $hdr_value;
        }
        return $input;
    }

    /**
     * Set the object's end-of-line and define the constant if applicable.
     *
     * @param string $eol End Of Line sequence
     *
     * @return void
     * @access private
     */
    function _setEOL($eol)
    {
        $this->_eol = $eol;
        if (!defined('MAIL_MIME_CRLF')) {
            define('MAIL_MIME_CRLF', $this->_eol, true);
        }
    }

    

} // End of class
