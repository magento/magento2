<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Exception;

/**
 * Helper for retrieving avatars from gravatar.com
 */
class Gravatar extends AbstractHtmlElement
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
     * Attributes for HTML image tag
     *
     * @var array
     */
    protected $attribs;

    /**
     * Email Address
     *
     * @var string
     */
    protected $email;

    /**
     * True or false if the email address passed is already an MD5 hash
     *
     * @var bool
     */
    protected $emailIsHashed;

    /**
     * Options
     *
     * @var array
     */
    protected $options = array(
        'img_size'    => 80,
        'default_img' => self::DEFAULT_MM,
        'rating'      => self::RATING_G,
        'secure'      => null,
    );

    /**
     * Returns an avatar from gravatar's service.
     *
     * $options may include the following:
     * - 'img_size' int height of img to return
     * - 'default_img' string img to return if email address has not found
     * - 'rating' string rating parameter for avatar
     * - 'secure' bool load from the SSL or Non-SSL location
     *
     * @see    http://pl.gravatar.com/site/implement/url
     * @see    http://pl.gravatar.com/site/implement/url More information about gravatar's service.
     * @param  string|null $email   Email address.
     * @param  null|array  $options Options
     * @param  array       $attribs Attributes for image tag (title, alt etc.)
     * @return Gravatar
     */
    public function __invoke($email = "", $options = array(), $attribs = array())
    {
        if (!empty($email)) {
            $this->setEmail($email);
        }
        if (!empty($options)) {
            $this->setOptions($options);
        }
        if (!empty($attribs)) {
            $this->setAttribs($attribs);
        }

        return $this;
    }

    /**
     * Return valid image tag
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getImgTag();
    }

    /**
     * Configure state
     *
     * @param  array $options
     * @return Gravatar
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
     * Get avatar url (including size, rating and default image options)
     *
     * @return string
     */
    protected function getAvatarUrl()
    {
        $src = $this->getGravatarUrl()
            . '/'   . ($this->emailIsHashed ? $this->getEmail() : md5($this->getEmail()))
            . '?s=' . $this->getImgSize()
            . '&d=' . $this->getDefaultImg()
            . '&r=' . $this->getRating();
        return $src;
    }

    /**
     * Get URL to gravatar's service.
     *
     * @return string URL
     */
    protected function getGravatarUrl()
    {
        return ($this->getSecure() === false) ? self::GRAVATAR_URL : self::GRAVATAR_URL_SECURE;
    }

    /**
     * Return valid image tag
     *
     * @return string
     */
    public function getImgTag()
    {
        $this->setSrcAttribForImg();
        $html = '<img'
            . $this->htmlAttribs($this->getAttribs())
            . $this->getClosingBracket();

        return $html;
    }

    /**
     * Set attribs for image tag
     *
     * Warning! You shouldn't set src attrib for image tag.
     * This attrib is overwritten in protected method setSrcAttribForImg().
     * This method(_setSrcAttribForImg) is called in public method getImgTag().
     *
     * @param  array $attribs
     * @return Gravatar
     */
    public function setAttribs(array $attribs)
    {
        $this->attribs = $attribs;
        return $this;
    }

    /**
     * Get attribs of image
     *
     * Warning!
     * If you set src attrib, you get it, but this value will be overwritten in
     * protected method setSrcAttribForImg(). And finally your get other src
     * value!
     *
     * @return array
     */
    public function getAttribs()
    {
        return $this->attribs;
    }

    /**
     * Set default img
     *
     * Can be either an absolute URL to an image, or one of the DEFAULT_* constants
     *
     * @link   http://pl.gravatar.com/site/implement/url More information about default image.
     * @param  string $defaultImg
     * @return Gravatar
     */
    public function setDefaultImg($defaultImg)
    {
        $this->options['default_img'] = urlencode($defaultImg);
        return $this;
    }

    /**
     * Get default img
     *
     * @return string
     */
    public function getDefaultImg()
    {
        return $this->options['default_img'];
    }

    /**
     * Set email address
     *
     * @param  string $email
     * @return Gravatar
     */
    public function setEmail($email)
    {
        $this->emailIsHashed = (bool) preg_match('/^[A-Za-z0-9]{32}$/', $email);
        $this->email = strtolower(trim($email));
        return $this;
    }

    /**
     * Get email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set img size in pixels
     *
     * @param  int $imgSize Size of img must be between 1 and 512
     * @return Gravatar
     */
    public function setImgSize($imgSize)
    {
        $this->options['img_size'] = (int) $imgSize;
        return $this;
    }

    /**
     * Get img size
     *
     * @return int The img size
     */
    public function getImgSize()
    {
        return $this->options['img_size'];
    }

    /**
     *  Set rating value
     *
     * Must be one of the RATING_* constants
     *
     * @link   http://pl.gravatar.com/site/implement/url More information about rating.
     * @param  string $rating Value for rating. Allowed values are: g, px, r,x
     * @return Gravatar
     * @throws Exception\DomainException
     */
    public function setRating($rating)
    {
        switch ($rating) {
            case self::RATING_G:
            case self::RATING_PG:
            case self::RATING_R:
            case self::RATING_X:
                $this->options['rating'] = $rating;
                break;
            default:
                throw new Exception\DomainException(sprintf(
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
        return $this->options['rating'];
    }

    /**
     * Load from an SSL or No-SSL location?
     *
     * @param  bool $flag
     * @return Gravatar
     */
    public function setSecure($flag)
    {
        $this->options['secure'] = ($flag === null) ? null : (bool) $flag;
        return $this;
    }

    /**
     * Get an SSL or a No-SSL location
     *
     * @return bool
     */
    public function getSecure()
    {
        if ($this->options['secure'] === null) {
            return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        }

        return $this->options['secure'];
    }

    /**
     * Set src attrib for image.
     *
     * You shouldn't set an own url value!
     * It sets value, uses protected method getAvatarUrl.
     *
     * If already exists, it will be overwritten.
     *
     * @return void
     */
    protected function setSrcAttribForImg()
    {
        $attribs        = $this->getAttribs();
        $attribs['src'] = $this->getAvatarUrl();
        $this->setAttribs($attribs);
    }
}
