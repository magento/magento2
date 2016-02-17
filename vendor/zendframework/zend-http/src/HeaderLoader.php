<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http;

use Zend\Loader\PluginClassLoader;

/**
 * Plugin Class Loader implementation for HTTP headers
 */
class HeaderLoader extends PluginClassLoader
{
    /**
     * @var array Pre-aliased Header plugins
     */
    protected $plugins = array(
        'accept'             => 'Zend\Http\Header\Accept',
        'acceptcharset'      => 'Zend\Http\Header\AcceptCharset',
        'acceptencoding'     => 'Zend\Http\Header\AcceptEncoding',
        'acceptlanguage'     => 'Zend\Http\Header\AcceptLanguage',
        'acceptranges'       => 'Zend\Http\Header\AcceptRanges',
        'age'                => 'Zend\Http\Header\Age',
        'allow'              => 'Zend\Http\Header\Allow',
        'authenticationinfo' => 'Zend\Http\Header\AuthenticationInfo',
        'authorization'      => 'Zend\Http\Header\Authorization',
        'cachecontrol'       => 'Zend\Http\Header\CacheControl',
        'connection'         => 'Zend\Http\Header\Connection',
        'contentdisposition' => 'Zend\Http\Header\ContentDisposition',
        'contentencoding'    => 'Zend\Http\Header\ContentEncoding',
        'contentlanguage'    => 'Zend\Http\Header\ContentLanguage',
        'contentlength'      => 'Zend\Http\Header\ContentLength',
        'contentlocation'    => 'Zend\Http\Header\ContentLocation',
        'contentmd5'         => 'Zend\Http\Header\ContentMD5',
        'contentrange'       => 'Zend\Http\Header\ContentRange',
        'contenttransferencoding' => 'Zend\Http\Header\ContentTransferEncoding',
        'contenttype'        => 'Zend\Http\Header\ContentType',
        'cookie'             => 'Zend\Http\Header\Cookie',
        'date'               => 'Zend\Http\Header\Date',
        'etag'               => 'Zend\Http\Header\Etag',
        'expect'             => 'Zend\Http\Header\Expect',
        'expires'            => 'Zend\Http\Header\Expires',
        'from'               => 'Zend\Http\Header\From',
        'host'               => 'Zend\Http\Header\Host',
        'ifmatch'            => 'Zend\Http\Header\IfMatch',
        'ifmodifiedsince'    => 'Zend\Http\Header\IfModifiedSince',
        'ifnonematch'        => 'Zend\Http\Header\IfNoneMatch',
        'ifrange'            => 'Zend\Http\Header\IfRange',
        'ifunmodifiedsince'  => 'Zend\Http\Header\IfUnmodifiedSince',
        'keepalive'          => 'Zend\Http\Header\KeepAlive',
        'lastmodified'       => 'Zend\Http\Header\LastModified',
        'location'           => 'Zend\Http\Header\Location',
        'maxforwards'        => 'Zend\Http\Header\MaxForwards',
        'origin'             => 'Zend\Http\Header\Origin',
        'pragma'             => 'Zend\Http\Header\Pragma',
        'proxyauthenticate'  => 'Zend\Http\Header\ProxyAuthenticate',
        'proxyauthorization' => 'Zend\Http\Header\ProxyAuthorization',
        'range'              => 'Zend\Http\Header\Range',
        'referer'            => 'Zend\Http\Header\Referer',
        'refresh'            => 'Zend\Http\Header\Refresh',
        'retryafter'         => 'Zend\Http\Header\RetryAfter',
        'server'             => 'Zend\Http\Header\Server',
        'setcookie'          => 'Zend\Http\Header\SetCookie',
        'te'                 => 'Zend\Http\Header\TE',
        'trailer'            => 'Zend\Http\Header\Trailer',
        'transferencoding'   => 'Zend\Http\Header\TransferEncoding',
        'upgrade'            => 'Zend\Http\Header\Upgrade',
        'useragent'          => 'Zend\Http\Header\UserAgent',
        'vary'               => 'Zend\Http\Header\Vary',
        'via'                => 'Zend\Http\Header\Via',
        'warning'            => 'Zend\Http\Header\Warning',
        'wwwauthenticate'    => 'Zend\Http\Header\WWWAuthenticate'
    );
}
