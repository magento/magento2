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
 * @package    Zend_Tag
 * @subpackage Cloud
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: HtmlCloud.php 20104 2010-01-06 21:26:01Z matthew $
 */

/**
 * @see Zend_Tag_Cloud_Decorator_Cloud
 */
#require_once 'Zend/Tag/Cloud/Decorator/Cloud.php';

/**
 * Simple HTML decorator for clouds
 *
 * @category  Zend
 * @package   Zend_Tag
 * @uses      Zend_Tag_Cloud_Decorator_Cloud
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tag_Cloud_Decorator_HtmlCloud extends Zend_Tag_Cloud_Decorator_Cloud
{
    /**
     * @var string Encoding to use
     */
    protected $_encoding = 'UTF-8';

    /**
     * List of HTML tags
     *
     * @var array
     */
    protected $_htmlTags = array(
        'ul' => array('class' => 'Zend_Tag_Cloud')
    );

    /**
     * Separator for the single tags
     *
     * @var string
     */
    protected $_separator = ' ';

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->_encoding;
    }

    /**
     * Set encoding
     *
     * @param string
     * @return Zend_Tag_Cloud_Decorator_HtmlCloud
     */
    public function setEncoding($value)
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Set the HTML tags surrounding all tags
     *
     * @param  array $htmlTags
     * @return Zend_Tag_Cloud_Decorator_HtmlCloud
     */
    public function setHtmlTags(array $htmlTags)
    {
        $this->_htmlTags = $htmlTags;
        return $this;
    }

    /**
     * Retrieve HTML tag map
     *
     * @return array
     */
    public function getHtmlTags()
    {
        return $this->_htmlTags;
    }

    /**
     * Set the separator between the single tags
     *
     * @param  string
     * @return Zend_Tag_Cloud_Decorator_HtmlCloud
     */
    public function setSeparator($separator)
    {
        $this->_separator = $separator;
        return $this;
    }

    /**
     * Get tag separator
     *
     * @return string
     */
    public function getSeparator()
    {
        return $this->_separator;
    }

    /**
     * Defined by Zend_Tag_Cloud_Decorator_Cloud
     *
     * @param  array $tags
     * @return string
     */
    public function render(array $tags)
    {
        $cloudHtml = implode($this->getSeparator(), $tags);

        $enc = $this->getEncoding();
        foreach ($this->getHtmlTags() as $key => $data) {
            if (is_array($data)) {
                $htmlTag    = $key;
                $attributes = '';

                foreach ($data as $param => $value) {
                    $attributes .= ' ' . $param . '="' . htmlspecialchars($value, ENT_COMPAT, $enc) . '"';
                }
            } else {
                $htmlTag    = $data;
                $attributes = '';
            }

            $cloudHtml = sprintf('<%1$s%3$s>%2$s</%1$s>', $htmlTag, $cloudHtml, $attributes);
        }

        return $cloudHtml;
    }
}
