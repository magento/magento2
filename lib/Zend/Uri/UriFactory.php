<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Uri
 */

namespace Zend\Uri;

use Zend\Uri\Uri;

/**
 * URI Factory Class
 *
 * The URI factory can be used to generate URI objects from strings, using a
 * different URI subclass depending on the input URI scheme. New scheme-specific
 * classes can be registered using the registerScheme() method.
 *
 * Note that this class contains only static methods and should not be
 * instantiated
 *
 * @category  Zend
 * @package   Zend_Uri
 */
abstract class UriFactory
{
    /**
     * Registered scheme-specific classes
     *
     * @var array
     */
     protected static $schemeClasses = array(
        'http'   => 'Zend\Uri\Http',
        'https'  => 'Zend\Uri\Http',
        'mailto' => 'Zend\Uri\Mailto',
        'file'   => 'Zend\Uri\File',
        'urn'    => 'Zend\Uri\Uri',
        'tag'    => 'Zend\Uri\Uri',
    );

    /**
     * Register a scheme-specific class to be used
     *
     * @param string $scheme
     * @param string $class
     */
    public static function registerScheme($scheme, $class)
    {
        $scheme = strtolower($scheme);
        static::$schemeClasses[$scheme] = $class;
    }

    /**
     * Unregister a scheme
     *
     * @param string $scheme
     */
    public static function unregisterScheme($scheme)
    {
        $scheme = strtolower($scheme);
        if (isset(static::$schemeClasses[$scheme])) {
            unset(static::$schemeClasses[$scheme]);
        }
    }

    /**
     * Create a URI from a string
     *
     * @param  string $uriString
     * @param  string $defaultScheme
     * @throws Exception\InvalidArgumentException
     * @return \Zend\Uri\Uri
     */
    public static function factory($uriString, $defaultScheme = null)
    {
        if (!is_string($uriString)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Expecting a string, received "%s"',
                (is_object($uriString) ? get_class($uriString) : gettype($uriString))
            ));
        }

        $uri    = new Uri($uriString);
        $scheme = strtolower($uri->getScheme());
        if (!$scheme && $defaultScheme) {
            $scheme = $defaultScheme;
        }

        if ($scheme && ! isset(static::$schemeClasses[$scheme])) {
            throw new Exception\InvalidArgumentException(sprintf(
                    'no class registered for scheme "%s"',
                    $scheme
                ));
        }
        if ($scheme && isset(static::$schemeClasses[$scheme])) {
            $class = static::$schemeClasses[$scheme];
            $uri = new $class($uri);
            if (! $uri instanceof UriInterface) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'class "%s" registered for scheme "%s" does not implement Zend\Uri\UriInterface',
                    $class,
                    $scheme
                ));
            }
        }

        return $uri;
    }
}
