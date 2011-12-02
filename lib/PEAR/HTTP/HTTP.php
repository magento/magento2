<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * HTTP
 *
 * PHP versions 4 and 5
 *
 * @category  HTTP
 * @package   HTTP
 * @author    Stig Bakken <ssb@fast.no>
 * @author    Sterling Hughes <sterling@php.net>
 * @author    Tomas V.V.Cox <cox@idecnet.com>
 * @author    Richard Heyes <richard@php.net>
 * @author    Philippe Jausions <jausions@php.net>
 * @author    Michael Wallner <mike@php.net>
 * @copyright 2002-2008 The Authors
 * @license   http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version   CVS: $Id: HTTP.php,v 1.56 2008/08/31 20:15:43 jausions Exp $
 * @link      http://pear.php.net/package/HTTP
 */

/**
 * Miscellaneous HTTP Utilities
 *
 * PEAR::HTTP provides static shorthand methods for generating HTTP dates,
 * issueing HTTP HEAD requests, building absolute URIs, firing redirects and
 * negotiating user preferred language.
 *
 * @category HTTP
 * @package  HTTP
 * @author   Stig Bakken <ssb@fast.no>
 * @author   Sterling Hughes <sterling@php.net>
 * @author   Tomas V.V.Cox <cox@idecnet.com>
 * @author   Richard Heyes <richard@php.net>
 * @author   Philippe Jausions <jausions@php.net>
 * @author   Michael Wallner <mike@php.net>
 * @license  http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @abstract
 * @version  Release: $Revision: 1.56 $
 * @link     http://pear.php.net/package/HTTP
 */
class HTTP
{
    /**
     * Formats a RFC compliant GMT date HTTP header.  This function honors the
     * "y2k_compliance" php.ini directive and formats the GMT date corresponding
     * to either RFC850 or RFC822.
     *
     * @param mixed $time unix timestamp or date (default = current time)
     *
     * @return mixed  GMT date string, or false for an invalid $time parameter
     * @access public
     * @static
     */
    function Date($time = null)
    {
        if (!isset($time)) {
            $time = time();
        } elseif (!is_numeric($time) && (-1 === $time = strtotime($time))) {
            return false;
        }

        // RFC822 or RFC850
        $format = ini_get('y2k_compliance') ? 'D, d M Y' : 'l, d-M-y';

        return gmdate($format .' H:i:s \G\M\T', $time);
    }

    /**
     * Negotiates language with the user's browser through the Accept-Language
     * HTTP header or the user's host address.  Language codes are generally in
     * the form "ll" for a language spoken in only one country, or "ll-CC" for a
     * language spoken in a particular country.  For example, U.S. English is
     * "en-US", while British English is "en-UK".  Portugese as spoken in
     * Portugal is "pt-PT", while Brazilian Portugese is "pt-BR".
     *
     * Quality factors in the Accept-Language: header are supported, e.g.:
     *      Accept-Language: en-UK;q=0.7, en-US;q=0.6, no, dk;q=0.8
     *
     * <code>
     *  require_once 'HTTP.php';
     *  $langs = array(
     *      'en'    => 'locales/en',
     *      'en-US' => 'locales/en',
     *      'en-UK' => 'locales/en',
     *      'de'    => 'locales/de',
     *      'de-DE' => 'locales/de',
     *      'de-AT' => 'locales/de',
     *  );
     *  $neg = HTTP::negotiateLanguage($langs);
     *  $dir = $langs[$neg];
     * </code>
     *
     * @param array  $supported An associative array of supported languages,
     *                          whose values must evaluate to true.
     * @param string $default   The default language to use if none is found.
     *
     * @return string  The negotiated language result or the supplied default.
     * @static
     * @access public
     */
    function negotiateLanguage($supported, $default = 'en-US')
    {
        $supp = array();
        foreach ($supported as $lang => $isSupported) {
            if ($isSupported) {
                $supp[strtolower($lang)] = $lang;
            }
        }

        if (!count($supp)) {
            return $default;
        }

        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $match = HTTP::_matchAccept($_SERVER['HTTP_ACCEPT_LANGUAGE'],
                                        $supp);
            if (!is_null($match)) {
                return $match;
            }
        }

        if (isset($_SERVER['REMOTE_HOST'])) {
            $lang = strtolower(end($h = explode('.', $_SERVER['REMOTE_HOST'])));
            if (isset($supp[$lang])) {
                return $supp[$lang];
            }
        }

        return $default;
    }

    /**
     * Negotiates charset with the user's browser through the Accept-Charset
     * HTTP header.
     *
     * Quality factors in the Accept-Charset: header are supported, e.g.:
     *      Accept-Language: en-UK;q=0.7, en-US;q=0.6, no, dk;q=0.8
     *
     * <code>
     *  require_once 'HTTP.php';
     *  $charsets = array(
     *      'UTF-8',
     *      'ISO-8859-1',
     *  );
     *  $charset = HTTP::negotiateCharset($charsets);
     * </code>
     *
     * @param array  $supported An array of supported charsets
     * @param string $default   The default charset to use if none is found.
     *
     * @return string  The negotiated language result or the supplied default.
     * @static
     * @author Philippe Jausions <jausions@php.net>
     * @access public
     * @since  1.4.1
     */
    function negotiateCharset($supported, $default = 'ISO-8859-1')
    {
        $supp = array();
        foreach ($supported as $charset) {
            $supp[strtolower($charset)] = $charset;
        }

        if (!count($supp)) {
            return $default;
        }

        if (isset($_SERVER['HTTP_ACCEPT_CHARSET'])) {
            $match = HTTP::_matchAccept($_SERVER['HTTP_ACCEPT_CHARSET'],
                                        $supp);
            if (!is_null($match)) {
                return $match;
            }
        }

        return $default;
    }

    /**
     * Negotiates content type with the user's browser through the Accept
     * HTTP header.
     *
     * Quality factors in the Accept: header are supported, e.g.:
     *      Accept: application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8
     *
     * <code>
     *  require_once 'HTTP.php';
     *  $contentType = array(
     *      'application/xhtml+xml',
     *      'application/xml',
     *      'text/html',
     *      'text/plain',
     *  );
     *  $mime = HTTP::negotiateContentType($contentType);
     * </code>
     *
     * @param array  $supported An associative array of supported MIME types.
     * @param string $default   The default type to use if none match.
     *
     * @return string  The negotiated MIME type result or the supplied default.
     * @static
     * @author Philippe Jausions <jausions@php.net>
     * @access public
     * @since  1.4.1
     */
    function negotiateMimeType($supported, $default)
    {
        $supp = array();
        foreach ($supported as $type) {
            $supp[strtolower($type)] = $type;
        }

        if (!count($supp)) {
            return $default;
        }

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            $accepts = HTTP::_sortAccept($_SERVER['HTTP_ACCEPT']);

            foreach ($accepts as $type => $q) {
                if (substr($type, -2) != '/*') {
                    if (isset($supp[$type])) {
                        return $supp[$type];
                    }
                    continue;
                }
                if ($type == '*/*') {
                    return array_shift($supp);
                }
                list($general, $specific) = explode('/', $type);
                $general .= '/';
                $len = strlen($general);
                foreach ($supp as $mime => $t) {
                    if (strncasecmp($general, $mime, $len) == 0) {
                        return $t;
                    }
                }
            }
        }

        return $default;
    }

    /**
     * Parses a weighed "Accept" HTTP header and matches it against a list
     * of supported options
     *
     * @param string  $header    The HTTP "Accept" header to parse
     * @param array   $supported A list of supported values
     *
     * @return string|NULL  a matched option, or NULL if no match
     * @access private
     * @static
     */
    function _matchAccept($header, $supported)
    {
        $matches = HTTP::_sortAccept($header);
        foreach ($matches as $key => $q) {
            if (isset($supported[$key])) {
                return $supported[$key];
            }
        }
        // If any (i.e. "*") is acceptable, return the first supported format 
        if (isset($matches['*'])) {
            return array_shift($supported);
        }
        return null;
    }

    /**
     * Parses and sorts a weighed "Accept" HTTP header
     *
     * @param string  $header The HTTP "Accept" header to parse
     *
     * @return array  a sorted list of "accept" options
     * @access private
     * @static
     */
    function _sortAccept($header)
    {
        $matches = array();
        foreach (explode(',', $header) as $option) {
            $option = array_map('trim', explode(';', $option));

            $l = strtolower($option[0]);
            if (isset($option[1])) {
                $q = (float) str_replace('q=', '', $option[1]);
            } else {
                $q = null;
                // Assign default low weight for generic values
                if ($l == '*/*') {
                    $q = 0.01;
                } elseif (substr($l, -1) == '*') {
                    $q = 0.02;
                }
            }
            // Unweighted values, get high weight by their position in the
            // list 
            $matches[$l] = isset($q) ? $q : 1000 - count($matches);
        }
        arsort($matches, SORT_NUMERIC);
        return $matches;
    }

    /**
     * Sends a "HEAD" HTTP command to a server and returns the headers
     * as an associative array.
     *
     * Example output could be:
     * <code>
     *     Array
     *     (
     *         [response_code] => 200          // The HTTP response code
     *         [response] => HTTP/1.1 200 OK   // The full HTTP response string
     *         [Date] => Fri, 11 Jan 2002 01:41:44 GMT
     *         [Server] => Apache/1.3.20 (Unix) PHP/4.1.1
     *         [X-Powered-By] => PHP/4.1.1
     *         [Connection] => close
     *         [Content-Type] => text/html
     *     )
     * </code>
     *
     * @param string  $url     A valid URL, e.g.: http://pear.php.net/credits.php
     * @param integer $timeout Timeout in seconds (default = 10)
     *
     * @return array  Returns associative array of response headers on success
     *                or PEAR error on failure.
     * @static
     * @access public
     * @see HTTP_Client::head()
     * @see HTTP_Request
     */
    function head($url, $timeout = 10)
    {
        $p = parse_url($url);
        if (!isset($p['scheme'])) {
            $p = parse_url(HTTP::absoluteURI($url));
        } elseif ($p['scheme'] != 'http') {
            return HTTP::raiseError('Unsupported protocol: '. $p['scheme']);
        }

        $port = isset($p['port']) ? $p['port'] : 80;

        if (!$fp = @fsockopen($p['host'], $port, $eno, $estr, $timeout)) {
            return HTTP::raiseError("Connection error: $estr ($eno)");
        }

        $path  = !empty($p['path']) ? $p['path'] : '/';
        $path .= !empty($p['query']) ? '?' . $p['query'] : '';

        fputs($fp, "HEAD $path HTTP/1.0\r\n");
        fputs($fp, 'Host: ' . $p['host'] . ':' . $port . "\r\n");
        fputs($fp, "Connection: close\r\n\r\n");

        $response = rtrim(fgets($fp, 4096));
        if (preg_match("|^HTTP/[^\s]*\s(.*?)\s|", $response, $status)) {
            $headers['response_code'] = $status[1];
        }
        $headers['response'] = $response;

        while ($line = fgets($fp, 4096)) {
            if (!trim($line)) {
                break;
            }
            if (($pos = strpos($line, ':')) !== false) {
                $header = substr($line, 0, $pos);
                $value  = trim(substr($line, $pos + 1));

                $headers[$header] = $value;
            }
        }
        fclose($fp);
        return $headers;
    }

    /**
     * This function redirects the client. This is done by issuing
     * a "Location" header and exiting if wanted.  If you set $rfc2616 to true
     * HTTP will output a hypertext note with the location of the redirect.
     *
     * @param string $url     URL where the redirect should go to.
     * @param bool   $exit    Whether to exit immediately after redirection.
     * @param bool   $rfc2616 Wheter to output a hypertext note where we're
     *                        redirecting to (Redirecting to
     *                        <a href="...">...</a>.)
     *
     * @return boolean  Returns TRUE on succes (or exits) or FALSE if headers
     *                  have already been sent.
     * @static
     * @access public
     */
    function redirect($url, $exit = true, $rfc2616 = false)
    {
        if (headers_sent()) {
            return false;
        }

        $url = HTTP::absoluteURI($url);
        header('Location: '. $url);

        if ($rfc2616 && isset($_SERVER['REQUEST_METHOD'])
            && $_SERVER['REQUEST_METHOD'] != 'HEAD') {
            echo '
<p>Redirecting to: <a href="'.str_replace('"', '%22', $url).'">'
                 .htmlspecialchars($url).'</a>.</p>
<script type="text/javascript">
//<![CDATA[
if (location.replace == null) {
    location.replace = location.assign;
}
location.replace("'.str_replace('"', '\\"', $url).'");
// ]]>
</script>';
        }
        if ($exit) {
            exit;
        }
        return true;
    }

    /**
     * This function returns the absolute URI for the partial URL passed.
     * The current scheme (HTTP/HTTPS), host server, port, current script
     * location are used if necessary to resolve any relative URLs.
     *
     * Offsets potentially created by PATH_INFO are taken care of to resolve
     * relative URLs to the current script.
     *
     * You can choose a new protocol while resolving the URI.  This is
     * particularly useful when redirecting a web browser using relative URIs
     * and to switch from HTTP to HTTPS, or vice-versa, at the same time.
     *
     * @param string  $url      Absolute or relative URI the redirect should
     *                          go to.
     * @param string  $protocol Protocol to use when redirecting URIs.
     * @param integer $port     A new port number.
     *
     * @return string  The absolute URI.
     * @author Philippe Jausions <Philippe.Jausions@11abacus.com>
     * @static
     * @access public
     */
    function absoluteURI($url = null, $protocol = null, $port = null)
    {
        // filter CR/LF
        $url = str_replace(array("\r", "\n"), ' ', $url);

        // Mess around protocol and port with already absolute URIs
        if (preg_match('!^([a-z0-9]+)://!i', $url)) {
            if (empty($protocol) && empty($port)) {
                return $url;
            }
            if (!empty($protocol)) {
                $url = $protocol .':'. end($array = explode(':', $url, 2));
            }
            if (!empty($port)) {
                $url = preg_replace('!^(([a-z0-9]+)://[^/:]+)(:[\d]+)?!i',
                                    '\1:'. $port, $url);
            }
            return $url;
        }

        $host = 'localhost';
        if (!empty($_SERVER['HTTP_HOST'])) {
            list($host) = explode(':', $_SERVER['HTTP_HOST']);
        } elseif (!empty($_SERVER['SERVER_NAME'])) {
            list($host) = explode(':', $_SERVER['SERVER_NAME']);
        }

        if (empty($protocol)) {
            if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
                $protocol = 'https';
            } else {
                $protocol = 'http';
            }
            if (!isset($port) || $port != intval($port)) {
                $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : 80;
            }
        }

        if ($protocol == 'http' && $port == 80) {
            unset($port);
        }
        if ($protocol == 'https' && $port == 443) {
            unset($port);
        }

        $server = $protocol.'://'.$host.(isset($port) ? ':'.$port : '');

        $uriAll = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']
                                                 : $_SERVER['PHP_SELF'];
        if (false !== ($q = strpos($uriAll, '?'))) {
            $uriBase = substr($uriAll, 0, $q);
        } else {
            $uriBase = $uriAll;
        }
        if (!strlen($url) || $url{0} == '#') {
            $url = $uriAll.$url;
        } elseif ($url{0} == '?') {
            $url = $uriBase.$url;
        }
        if ($url{0} == '/') {
            return $server . $url;
        }

        // Adjust for PATH_INFO if needed
        if (isset($_SERVER['PATH_INFO']) && strlen($_SERVER['PATH_INFO'])) {
            $path = dirname(substr($uriBase, 0,
                                   -strlen($_SERVER['PATH_INFO'])));
        } else {
            /**
             * Fixes bug #12672 PHP_SELF ending on / causes incorrect redirects
             *
             * @link http://pear.php.net/bugs/12672
             */
            $path = dirname($uriBase.'-');
        }

        if (substr($path = strtr($path, '\\', '/'), -1) != '/') {
            $path .= '/';
        }

        return $server . $path . $url;
    }

    /**
     * Raise Error
     *
     * Lazy raising of PEAR_Errors.
     *
     * @param mixed   $error Error
     * @param integer $code  Error code
     *
     * @return object  PEAR_Error
     * @static
     * @access protected
     */
    function raiseError($error = null, $code = null)
    {
        include_once 'PEAR.php';
        return PEAR::raiseError($error, $code);
    }
}

?>