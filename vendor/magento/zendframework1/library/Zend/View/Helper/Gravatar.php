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
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Doctype.php 16971 2009-07-22 18:05:45Z mikaelkael $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_HtmlElement */
#require_once 'Zend/View/Helper/HtmlElement.php';

/**
 * Helper for retrieving avatars from gravatar.com
 *
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @link http://pl.gravatar.com/site/implement/url
 */
class Zend_View_Helper_Gravatar extends Zend_View_Helper_HtmlElement
{

    /**
     * URL to gravatar service
     */
    const GRAVATAR_URL = 'http://www.gravatar.com/avatar';
    /**
     * Secure URL to gravatar service
     */
    const GRAVATAR_URL_SECURE = 'https://secure.gravatar.com/avatar';

    /**
     * Gravatar rating
     */
    const RATING_G  = 'g';
    const RATING_PG = 'pg';
    const RATING_R  = 'r';
    const RATING_X  = 'x';

    /**
     * Default gravatar image value constants
     */
    const DEFAULT_404       = '404';
    const DEFAULT_MM        = 'mm';
    const DEFAULT_IDENTICON = 'identicon';
    const DEFAULT_MONSTERID = 'monsterid';
    const DEFAULT_WAVATAR   = 'wavatar';

    /**
     * Options
     *
     * @var array
     */
    protected $_options = array(
        'img_size'    => 80,
        'default_img' => self::DEFAULT_MM,
        'rating'      => self::RATING_G,
        'secure'      => null,
    );

    /**
     * Email Adress
     *
     * @var string
     */
    protected $_email;

    /**
     * Attributes for HTML image tag
     *
     * @var array
     */
    protected $_attribs;

    /**
     * Returns an avatar from gravatar's service.
     *
     * $options may include the following:
     * - 'img_size' int height of img to return
     * - 'default_img' string img to return if email adress has not found
     * - 'rating' string rating parameter for avatar
     * - 'secure' bool load from the SSL or Non-SSL location
     *
     * @see    http://pl.gravatar.com/site/implement/url
     * @see    http://pl.gravatar.com/site/implement/url More information about gravatar's service.
     * @param  string|null $email Email adress.
     * @param  null|array $options Options
     * @param  array $attribs Attributes for image tag (title, alt etc.)
     * @return Zend_View_Helper_Gravatar
     */
    public function gravatar($email = "", $options = array(), $attribs = array())
    {
        $this->setEmail($email);
        $this->setOptions($options);
        $this->setAttribs($attribs);
        return $this;
    }

    /**
     * Configure state
     *
     * @param  array $options
     * @return Zend_View_Helper_Gravatar
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
        return $this;
    }

    /**
     * Get img size
     *
     * @return int The img size
     */
    public function getImgSize()
    {
        return $this->_options['img_size'];
    }

    /**
     * Set img size in pixels
     *
     * @param int $imgSize Size of img must be between 1 and 512
     * @return Zend_View_Helper_Gravatar
     */
    public function setImgSize($imgSize)
    {
        $this->_options['img_size'] = (int) $imgSize;
        return $this;
    }

    /**
     * Get default img
     *
     * @return string
     */
    public function getDefaultImg()
    {
        return $this->_options['default_img'];
    }

    /**
     * Set default img
     *
     * Can be either an absolute URL to an image, or one of the DEFAULT_* constants
     *
     * @param string $defaultImg
     * @link http://pl.gravatar.com/site/implement/url More information about default image.
     * @return Zend_View_Helper_Gravatar
     */
    public function setDefaultImg($defaultImg)
    {
        $this->_options['default_img'] = urlencode($defaultImg);
        return $this;
    }

    /**
     *  Set rating value
     *
     * Must be one of the RATING_* constants
     *
     * @param string $rating Value for rating. Allowed values are: g, px, r,x
     * @link http://pl.gravatar.com/site/implement/url More information about rating.
     * @throws Zend_View_Exception
     */
    public function setRating($rating)
    {
        switch ($rating) {
            case self::RATING_G:
            case self::RATING_PG:
            case self::RATING_R:
            case self::RATING_X:
                $this->_options['rating'] = $rating;
                break;
            default:
                #require_once 'Zend/View/Exception.php';
                throw new Zend_View_Exception(sprintf(
                    'The rating value "%s" is not allowed',
                    $rating
                ));
        }
        return $this;
    }

    /**
     * Get rating value
     *
     * @return string
     */
    public function getRating()
    {
        return $this->_options['rating'];
    }

    /**
     * Set email adress
     *
     * @param string $email
     * @return Zend_View_Helper_Gravatar
     */
    public function setEmail( $email )
    {
        $this->_email = $email;
        return $this;
    }

    /**
     * Get email adress
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Load from an SSL or No-SSL location?
     *
     * @param bool $flag
     * @return Zend_View_Helper_Gravatar
     */
    public function setSecure($flag)
    {
        $this->_options['secure'] = ($flag === null) ? null : (bool) $flag;
        return $this;
    }

    /**
     * Get an SSL or a No-SSL location
     *
     * @return bool
     */
    public function getSecure()
    {
        if ($this->_options['secure'] === null) {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        }
        return $this->_options['secure'];
    }

    /**
     * Get attribs of image
     *
     * Warning!
     * If you set src attrib, you get it, but this value will be overwritten in
     * protected method _setSrcAttribForImg(). And finally your get other src
     * value!
     *
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * Set attribs for image tag
     *
     * Warning! You shouldn't set src attrib for image tag.
     * This attrib is overwritten in protected method _setSrcAttribForImg().
     * This method(_setSrcAttribForImg) is called in public method getImgTag().

     * @param array $attribs
     * @return Zend_View_Helper_Gravatar
     */
    public function setAttribs(array $attribs)
    {
        $this->_attribs = $attribs;
        return $this;
    }

    /**
     * Get URL to gravatar's service.
     *
     * @return string URL
     */
    protected function _getGravatarUrl()
    {
        return ($this->getSecure() === false) ? self::GRAVATAR_URL : self::GRAVATAR_URL_SECURE;
    }

    /**
     * Get avatar url (including size, rating and default image oprions)
     *
     * @return string
     */
    protected function _getAvatarUrl()
    {
        $src = $this->_getGravatarUrl()
             . '/'
             . md5(strtolower(trim($this->getEmail())))
             . '?s='
             . $this->getImgSize()
             . '&d='
             . $this->getDefaultImg()
             . '&r='
             . $this->getRating();
        return $src;
    }

    /**
     * Set src attrib for image.
     *
     * You shouldn't set a own url value!
     * It sets value, uses protected method _getAvatarUrl.
     *
     * If already exsist overwritten.
     */
    protected function _setSrcAttribForImg()
    {
        $attribs        = $this->getAttribs();
        $attribs['src'] = $this->_getAvatarUrl();
        $this->setAttribs($attribs);
    }

    /**
     * Return valid image tag
     *
     * @return string
     */
    public function getImgTag()
    {
        $this->_setSrcAttribForImg();
        $html = '<img'
              . $this->_htmlAttribs($this->getAttribs())
              . $this->getClosingBracket();

        return $html;
    }

    /**
     * Return valid image tag
     *
     * @return string
     */
    public function  __toString()
    {
        return $this->getImgTag();

    }
}
