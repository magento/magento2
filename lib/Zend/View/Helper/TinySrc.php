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
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_HtmlElement */
#require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * Helper for generating urls and/or image tags for use with tinysrc.net
 *
 * tinysrc.net provides an API for generating scaled, browser device-specific 
 * images. In essence, you pass the API the URL to an image on your own server,
 * and tinysrc.net then provides the appropriate image based on the device that
 * accesses it.
 *
 * Additionally, tinysrc.net allows you to specify additional configuration via 
 * the API:
 *
 * - image size. You may define this as:
 *   - explicit size
 *   - subtractive size (size of screen minus specified number of pixels)
 *   - percentage size (percentage of screen size))
 * - image format. This will convert the image to the given format; allowed 
 *   values are "png" or "jpeg". By default, gif images are converted to png.
 *
 * This helper allows you to specify all configuration options, as well as:
 *
 * - whether or not to generate the full image tag (or just the URL)
 * - base url to images (which should include the protocol, server, and 
 *   optionally port and base path)
 *
 * @see        http://tinysrc.net/
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_TinySrc extends Zend_View_Helper_HtmlElement
{
    const TINYSRC_BASE = 'http://i.tinysrc.mobi';

    /**
     * @var string Base URL for images
     */
    protected $_baseUrl;

    /**
     * @var bool Whether or not to create an image tag
     */
    protected $_createTagFlag = true;

    /**
     * @var string Default width and height
     */
    protected $_dimensions = '';

    /**
     * Default options
     *
     * Used when determining what options were passed, and needing to merge 
     * them with default options.
     * 
     * @var array
     */
    protected $_defaultOptions = array(
        'base_url'   => null,
        'format'     => null,
        'width'      => false,
        'height'     => false,
        'create_tag' => true,
    );

    /**
     * @var string Default image format to use
     */
    protected $_format = '';

    /**
     * Generate a link or image tag pointing to tinysrc.net
     * 
     * @param mixed $image 
     * @param array $options 
     * @return void
     */
    public function tinySrc($image = null, array $options = array())
    {
        if (null === $image) {
            return $this;
        }

        $defaultOptions = $this->_defaultOptions;
        $defaultOptions['create_tag'] = $this->createTag();
        $options = array_merge($defaultOptions, $options);

        $url = '/' . $this->_mergeBaseUrl($options) . ltrim($image, '/');

        $src = self::TINYSRC_BASE 
             . $this->_mergeFormat($options) 
             . $this->_mergeDimensions($options)
             . $url;

        if (!$options['create_tag']) {
            return $src;
        }

        foreach (array_keys($this->_defaultOptions) as $key) {
            switch ($key) {
                case 'width':
                case 'height':
                    if (!is_int($options[$key]) || !is_numeric($options[$key]) || $options[$key] < 0) {
                        unset($options[$key]);
                    }
                    break;
                default:
                    unset($options[$key]);
                    break;
            }
        }

        $options['src'] = $src;

        $tag = '<img' . $this->_htmlAttribs($options) . $this->getClosingBracket();
        return $tag;
    }

    /**
     * Set base URL for images
     * 
     * @param  string $url 
     * @return Zend_View_Helper_TinySrc
     */
    public function setBaseUrl($url)
    {
        $this->_baseUrl = rtrim($url, '/') . '/';
        return $this;
    }

    /**
     * Get base URL for images
     *
     * If none already set, uses the ServerUrl and BaseUrl view helpers to
     * determine the base URL to images.
     * 
     * @return string
     */
    public function getBaseUrl()
    {
        if (null === $this->_baseUrl) {
            $this->setBaseUrl($this->view->serverUrl($this->view->baseUrl()));
        }
        return $this->_baseUrl;
    }

    /**
     * Set default image format
     *
     * If set, this will set the default format to use on all images.
     * 
     * @param  null|string $format 
     * @return Zend_View_Helper_TinySrc
     * @throws Zend_View_Exception
     */
    public function setDefaultFormat($format = null)
    {
        if (null === $format) {
            $this->_format = '';
            return $this;
        }

        $format = strtolower($format);
        if (!in_array($format, array('png', 'jpeg'))) {
            #require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Invalid format; must be one of "jpeg" or "png"');
        }
        $this->_format = "/$format";
        return $this;
    }

    /**
     * Set default dimensions
     *
     * If null is specified for width, default dimensions will be cleared. If 
     * only width is specified, only width will be used. If either dimension
     * fails validation, an exception is raised.
     * 
     * @param  null|int|string $width 
     * @param  null|int|string $height 
     * @return Zend_View_Helper_TinySrc
     * @throws Zend_View_Exception
     */
    public function setDefaultDimensions($width = null, $height = null)
    {
        if (null === $width) {
            $this->_dimensions = '';
            return $this;
        }

        if (!$this->_validateDimension($width)) {
            #require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Invalid dimension; must be an integer, optionally preceded by "-" or "x"');
        }

        $this->_dimensions = "/$width";
        if (null === $height) {
            return $this;
        }

        if (!$this->_validateDimension($height)) {
            #require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('Invalid dimension; must be an integer, optionally preceded by "-" or "x"');
        }
        $this->_dimensions .= "/$height";
        return $this;
    }

    /**
     * Set state of "create tag" flag
     * 
     * @param  bool $flag 
     * @return Zend_View_Helper_TinySrc
     */
    public function setCreateTag($flag)
    {
        $this->_createTagFlag = (bool) $flag;
        return $this;
    }

    /**
     * Should the helper create an image tag?
     * 
     * @return bool
     */
    public function createTag()
    {
        return $this->_createTagFlag;
    }

    /**
     * Validate a dimension
     *
     * Dimensions may be integers, optionally preceded by '-' or 'x'.
     * 
     * @param  string $dim 
     * @return bool
     */
    protected function _validateDimension($dim)
    {
        if (!is_scalar($dim) || is_bool($dim)) {
            return false;
        }
        return preg_match('/^(-|x)?\d+$/', (string) $dim);
    }

    /**
     * Determine whether to use default base URL, or base URL from options
     * 
     * @param  array $options 
     * @return string
     */
    protected function _mergeBaseUrl(array $options)
    {
        if (null === $options['base_url']) {
            return $this->getBaseUrl();
        }
        return rtrim($options['base_url'], '/') . '/';
    }

    /**
     * Determine whether to use default format or format provided in options.
     * 
     * @param  array $options 
     * @return string
     */
    protected function _mergeFormat(array $options) 
    {
        if (in_array($options['format'], array('png', 'jpeg'))) {
            return '/' . $options['format'];
        }
        return $this->_format;
    }

    /**
     * Determine whether to use default dimensions, or those passed in options.
     * 
     * @param  array $options 
     * @return string
     */
    protected function _mergeDimensions(array $options)
    {
        if (!$this->_validateDimension($options['width'])) {
            return $this->_dimensions;
        }
        $dimensions = '/' . $options['width'];
        if (!$this->_validateDimension($options['height'])) {
            return $dimensions;
        }
        $dimensions .= '/' . $options['height'];
        return $dimensions;
    }
}
