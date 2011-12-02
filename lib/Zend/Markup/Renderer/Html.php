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
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Html.php 22286 2010-05-25 14:26:45Z matthew $
 */

/**
 * @see Zend_Filter_HtmlEntities
 */
#require_once 'Zend/Filter/HtmlEntities.php';
/**
 * @see Zend_Filter_PregReplace
 */
#require_once 'Zend/Filter/PregReplace.php';
/**
 * @see Zend_Filter_Callback
 */
#require_once 'Zend/Filter/Callback.php';
/**
 * @see Zend_Markup_Renderer_RendererAbstract
 */
#require_once 'Zend/Markup/Renderer/RendererAbstract.php';

/**
 * HTML renderer
 *
 * @category   Zend
 * @package    Zend_Markup
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Markup_Renderer_Html extends Zend_Markup_Renderer_RendererAbstract
{

    /**
     * Element groups
     *
     * @var array
     */
    protected $_groups = array(
        'block'        => array('block', 'inline', 'block-empty', 'inline-empty', 'list'),
        'inline'       => array('inline', 'inline-empty'),
        'list'         => array('list-item'),
        'list-item'    => array('inline', 'inline-empty', 'list'),
        'block-empty'  => array(),
        'inline-empty' => array(),
    );

    /**
     * The current group
     *
     * @var string
     */
    protected $_group = 'block';

    /**
     * Default attributes
     *
     * @var array
     */
    protected static $_defaultAttributes = array(
        'id'    => '',
        'class' => '',
        'style' => '',
        'lang'  => '',
        'title' => ''
    );


    /**
     * Constructor
     *
     * @param array|Zend_Config $options
     *
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        $this->_pluginLoader = new Zend_Loader_PluginLoader(array(
            'Zend_Markup_Renderer_Html' => 'Zend/Markup/Renderer/Html/'
        ));

        if (!isset($options['useDefaultMarkups']) && isset($options['useDefaultTags'])) {
            $options['useDefaultMarkups'] = $options['useDefaultTags'];
        }
        if (isset($options['useDefaultMarkups']) && ($options['useDefaultMarkups'] !== false)) {
            $this->_defineDefaultMarkups();
        } elseif (!isset($options['useDefaultMarkups'])) {
            $this->_defineDefaultMarkups();
        }

        parent::__construct($options);
    }

    /**
     * Define the default markups
     *
     * @return void
     */
    protected function _defineDefaultMarkups()
    {
        $this->_markups = array(
            'b' => array(
                'type'   => 10, // self::TYPE_REPLACE | self::TAG_NORMAL
                'tag'    => 'strong',
                'group'  => 'inline',
                'filter' => true,
            ),
            'u' => array(
                'type'        => 10,
                'tag'         => 'span',
                'attributes'  => array(
                    'style' => 'text-decoration: underline;',
                ),
                'group'       => 'inline',
                'filter'      => true,
            ),
            'i' => array(
                'type'   => 10,
                'tag'    => 'em',
                'group'  => 'inline',
                'filter' => true,
            ),
            'cite' => array(
                'type'   => 10,
                'tag'    => 'cite',
                'group'  => 'inline',
                'filter' => true,
            ),
            'del' => array(
                'type'   => 10,
                'tag'    => 'del',
                'group'  => 'inline',
                'filter' => true,
            ),
            'ins' => array(
                'type'   => 10,
                'tag'    => 'ins',
                'group'  => 'inline',
                'filter' => true,
            ),
            'sub' => array(
                'type'   => 10,
                'tag'    => 'sub',
                'group'  => 'inline',
                'filter' => true,
            ),
            'sup' => array(
                'type'   => 10,
                'tag'    => 'sup',
                'group'  => 'inline',
                'filter' => true,
            ),
            'span' => array(
                'type'   => 10,
                'tag'    => 'span',
                'group'  => 'inline',
                'filter' => true,
            ),
            'acronym'  => array(
                'type'   => 10,
                'tag'    => 'acronym',
                'group'  => 'inline',
                'filter' => true,
            ),
            // headings
            'h1' => array(
                'type'   => 10,
                'tag'    => 'h1',
                'group'  => 'inline',
                'filter' => true,
            ),
            'h2' => array(
                'type'   => 10,
                'tag'    => 'h2',
                'group'  => 'inline',
                'filter' => true,
            ),
            'h3' => array(
                'type'   => 10,
                'tag'    => 'h3',
                'group'  => 'inline',
                'filter' => true,
            ),
            'h4' => array(
                'type'   => 10,
                'tag'    => 'h4',
                'group'  => 'inline',
                'filter' => true,
            ),
            'h5' => array(
                'type'   => 10,
                'tag'    => 'h5',
                'group'  => 'inline',
                'filter' => true,
            ),
            'h6' => array(
                'type'   => 10,
                'tag'    => 'h6',
                'group'  => 'inline',
                'filter' => true,
            ),
            // callback tags
            'url' => array(
                'type'     => 6, // self::TYPE_CALLBACK | self::TAG_NORMAL
                'callback' => null,
                'group'    => 'inline',
                'filter'   => true,
            ),
            'img' => array(
                'type'     => 6,
                'callback' => null,
                'group'    => 'inline-empty',
                'filter'   => true,
            ),
            'code' => array(
                'type'     => 6,
                'callback' => null,
                'group'    => 'block-empty',
                'filter'   => false,
            ),
            'p' => array(
                'type'   => 10,
                'tag'    => 'p',
                'group'  => 'block',
                'filter' => true,
            ),
            'ignore' => array(
                'type'   => 10,
                'start'  => '',
                'end'    => '',
                'group'  => 'block-empty',
                'filter' => true,
            ),
            'quote' => array(
                'type'   => 10,
                'tag'    => 'blockquote',
                'group'  => 'block',
                'filter' => true,
            ),
            'list' => array(
                'type'     => 6,
                'callback' => null,
                'group'    => 'list',
                'filter'   => new Zend_Filter_PregReplace('/.*/is', ''),
            ),
            '*' => array(
                'type'   => 10,
                'tag'    => 'li',
                'group'  => 'list-item',
                'filter' => true,
            ),
            'hr' => array(
                'type'    => 9, // self::TYPE_REPLACE | self::TAG_SINGLE
                'tag'     => 'hr',
                'group'   => 'block',
                'empty'   => true,
            ),
            // aliases
            'bold' => array(
                'type' => 16,
                'name' => 'b',
            ),
            'strong' => array(
                'type' => 16,
                'name' => 'b',
            ),
            'italic' => array(
                'type' => 16,
                'name' => 'i',
            ),
            'em' => array(
                'type' => 16,
                'name' => 'i',
            ),
            'emphasized' => array(
                'type' => 16,
                'name' => 'i',
            ),
            'underline' => array(
                'type' => 16,
                'name' => 'u',
            ),
            'citation' => array(
                'type' => 16,
                'name' => 'cite',
            ),
            'deleted' => array(
                'type' => 16,
                'name' => 'del',
            ),
            'insert' => array(
                'type' => 16,
                'name' => 'ins',
            ),
            'strike' => array(
                'type' => 16,
                'name' => 's',
            ),
            's' => array(
                'type' => 16,
                'name' => 'del',
            ),
            'subscript' => array(
                'type' => 16,
                'name' => 'sub',
            ),
            'superscript' => array(
                'type' => 16,
                'name' => 'sup',
            ),
            'a' => array(
                'type' => 16,
                'name' => 'url',
            ),
            'image' => array(
                'type' => 16,
                'name' => 'img',
            ),
            'li' => array(
                'type' => 16,
                'name' => '*',
            ),
            'color' => array(
                'type' => 16,
                'name' => 'span',
            ),
        );
    }

    /**
     * Add the default filters
     *
     * @return void
     */
    public function addDefaultFilters()
    {
        $this->_defaultFilter = new Zend_Filter();

        $this->_defaultFilter->addFilter(new Zend_Filter_HtmlEntities(array('encoding' => self::getEncoding())));
        $this->_defaultFilter->addFilter(new Zend_Filter_Callback('nl2br'));
    }

    /**
     * Execute a replace token
     *
     * @param  Zend_Markup_Token $token
     * @param  array $markup
     * @return string
     */
    protected function _executeReplace(Zend_Markup_Token $token, $markup)
    {
        if (isset($markup['tag'])) {
            if (!isset($markup['attributes'])) {
                $markup['attributes'] = array();
            }
            $attrs = self::renderAttributes($token, $markup['attributes']);
            return "<{$markup['tag']}{$attrs}>{$this->_render($token)}</{$markup['tag']}>";
        }

        return parent::_executeReplace($token, $markup);
    }

    /**
     * Execute a single replace token
     *
     * @param  Zend_Markup_Token $token
     * @param  array $markup
     * @return string
     */
    protected function _executeSingleReplace(Zend_Markup_Token $token, $markup)
    {
        if (isset($markup['tag'])) {
            if (!isset($markup['attributes'])) {
                $markup['attributes'] = array();
            }
            $attrs = self::renderAttributes($token, $markup['attributes']);
            return "<{$markup['tag']}{$attrs} />";
        }
        return parent::_executeSingleReplace($token, $markup);
    }

    /**
     * Render some attributes
     *
     * @param  Zend_Markup_Token $token
     * @param  array $attributes
     * @return string
     */
    public static function renderAttributes(Zend_Markup_Token $token, array $attributes = array())
    {
        $attributes = array_merge(self::$_defaultAttributes, $attributes);

        $return = '';

        $tokenAttributes = $token->getAttributes();

        // correct style attribute
        if (isset($tokenAttributes['style'])) {
            $tokenAttributes['style'] = trim($tokenAttributes['style']);

            if ($tokenAttributes['style'][strlen($tokenAttributes['style']) - 1] != ';') {
                $tokenAttributes['style'] .= ';';
            }
        } else {
            $tokenAttributes['style'] = '';
        }

        // special treathment for 'align' and 'color' attribute
        if (isset($tokenAttributes['align'])) {
            $tokenAttributes['style'] .= 'text-align: ' . $tokenAttributes['align'] . ';';
            unset($tokenAttributes['align']);
        }
        if (isset($tokenAttributes['color']) && self::checkColor($tokenAttributes['color'])) {
            $tokenAttributes['style'] .= 'color: ' . $tokenAttributes['color'] . ';';
            unset($tokenAttributes['color']);
        }

        /*
         * loop through all the available attributes, and check if there is
         * a value defined by the token
         * if there is no value defined by the token, use the default value or
         * don't set the attribute
         */
        foreach ($attributes as $attribute => $value) {
            if (isset($tokenAttributes[$attribute]) && !empty($tokenAttributes[$attribute])) {
                $return .= ' ' . $attribute . '="' . htmlentities($tokenAttributes[$attribute],
                                                                  ENT_QUOTES,
                                                                  self::getEncoding()) . '"';
            } elseif (!empty($value)) {
                $return .= ' ' . $attribute . '="' . htmlentities($value, ENT_QUOTES, self::getEncoding()) . '"';
            }
        }

        return $return;
    }

    /**
     * Check if a color is a valid HTML color
     *
     * @param string $color
     *
     * @return bool
     */
    public static function checkColor($color)
    {
        /*
         * aqua, black, blue, fuchsia, gray, green, lime, maroon, navy, olive,
         * purple, red, silver, teal, white, and yellow.
         */
        $colors = array(
            'aqua', 'black', 'blue', 'fuchsia', 'gray', 'green', 'lime',
            'maroon', 'navy', 'olive', 'purple', 'red', 'silver', 'teal',
            'white', 'yellow'
        );

        if (in_array($color, $colors)) {
            return true;
        }

        if (preg_match('/\#[0-9a-f]{6}/i', $color)) {
            return true;
        }

        return false;
    }

    /**
     * Check if the URI is valid
     *
     * @param string $uri
     *
     * @return bool
     */
    public static function isValidUri($uri)
    {
        if (!preg_match('/^([a-z][a-z+\-.]*):/i', $uri, $matches)) {
            return false;
        }

        $scheme = strtolower($matches[1]);

        switch ($scheme) {
            case 'javascript':
                // JavaScript scheme is not allowed for security reason
                return false;

            case 'http':
            case 'https':
            case 'ftp':
                $components = @parse_url($uri);

                if ($components === false) {
                    return false;
                }

                if (!isset($components['host'])) {
                    return false;
                }

                return true;

            default:
                return true;
        }
    }
}
