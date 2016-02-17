<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Markup
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Loader_PluginLoader
 */
#require_once 'Zend/Loader/PluginLoader.php';

/**
 * @category   Zend
 * @package    Zend_Markup
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup
{
    const CALLBACK = 'callback';
    const REPLACE  = 'replace';


    /**
     * The parser loader
     *
     * @var Zend_Loader_PluginLoader
     */
    protected static $_parserLoader;

    /**
     * The renderer loader
     *
     * @var Zend_Loader_PluginLoader
     */
    protected static $_rendererLoader;


    /**
     * Disable instantiation of Zend_Markup
     */
    private function __construct() { }

    /**
     * Get the parser loader
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getParserLoader()
    {
        if (!(self::$_parserLoader instanceof Zend_Loader_PluginLoader)) {
            self::$_parserLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Markup_Parser' => 'Zend/Markup/Parser/',
            ));
        }

        return self::$_parserLoader;
    }

    /**
     * Get the renderer loader
     *
     * @return Zend_Loader_PluginLoader
     */
    public static function getRendererLoader()
    {
        if (!(self::$_rendererLoader instanceof Zend_Loader_PluginLoader)) {
            self::$_rendererLoader = new Zend_Loader_PluginLoader(array(
                'Zend_Markup_Renderer' => 'Zend/Markup/Renderer/',
            ));
        }

        return self::$_rendererLoader;
    }

    /**
     * Add a parser path
     *
     * @param  string $prefix
     * @param  string $path
     * @return Zend_Loader_PluginLoader
     */
    public static function addParserPath($prefix, $path)
    {
        return self::getParserLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * Add a renderer path
     *
     * @param  string $prefix
     * @param  string $path
     * @return Zend_Loader_PluginLoader
     */
    public static function addRendererPath($prefix, $path)
    {
        return self::getRendererLoader()->addPrefixPath($prefix, $path);
    }

    /**
     * Factory pattern
     *
     * @param  string $parser
     * @param  string $renderer
     * @param  array $options
     * @return Zend_Markup_Renderer_RendererAbstract
     */
    public static function factory($parser, $renderer = 'Html', array $options = array())
    {
        $parserClass   = self::getParserLoader()->load($parser);
        $rendererClass = self::getRendererLoader()->load($renderer);

        $parser            = new $parserClass();
        $options['parser'] = $parser;
        $renderer          = new $rendererClass($options);

        return $renderer;
    }
}
