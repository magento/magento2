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
 * @version    $Id: HtmlTag.php 20104 2010-01-06 21:26:01Z matthew $
 */

/**
 * @see Zend_Tag_Cloud_Decorator_Tag
 */
#require_once 'Zend/Tag/Cloud/Decorator/Tag.php';

/**
 * Simple HTML decorator for tags
 *
 * @category  Zend
 * @package   Zend_Tag
 * @uses      Zend_Tag_Cloud_Decorator_Tag
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tag_Cloud_Decorator_HtmlTag extends Zend_Tag_Cloud_Decorator_Tag
{
    /**
     * List of tags which get assigned to the inner element instead of
     * font-sizes.
     *
     * @var array
     */
    protected $_classList = null;

    /**
     * @var string Encoding to utilize
     */
    protected $_encoding = 'UTF-8';

    /**
     * Unit for the fontsize
     *
     * @var string
     */
    protected $_fontSizeUnit = 'px';

    /**
     * Allowed fontsize units
     *
     * @var array
     */
    protected $_alloweFontSizeUnits = array('em', 'ex', 'px', 'in', 'cm', 'mm', 'pt', 'pc', '%');

    /**
     * List of HTML tags
     *
     * @var array
     */
    protected $_htmlTags = array(
        'li'
    );

    /**
     * Maximum fontsize
     *
     * @var integer
     */
    protected $_maxFontSize = 20;

    /**
     * Minimum fontsize
     *
     * @var integer
     */
    protected $_minFontSize = 10;

    /**
     * Set a list of classes to use instead of fontsizes
     *
     * @param  array $classList
     * @throws Zend_Tag_Cloud_Decorator_Exception When the classlist is empty
     * @throws Zend_Tag_Cloud_Decorator_Exception When the classlist contains an invalid classname
     * @return Zend_Tag_Cloud_Decorator_HtmlTag
     */
    public function setClassList(array $classList = null)
    {
        if (is_array($classList)) {
            if (count($classList) === 0) {
                #require_once 'Zend/Tag/Cloud/Decorator/Exception.php';
                throw new Zend_Tag_Cloud_Decorator_Exception('Classlist is empty');
            }

            foreach ($classList as $class) {
                if (!is_string($class)) {
                    #require_once 'Zend/Tag/Cloud/Decorator/Exception.php';
                    throw new Zend_Tag_Cloud_Decorator_Exception('Classlist contains an invalid classname');
                }
            }
        }

        $this->_classList = $classList;
        return $this;
    }

    /**
     * Get class list
     *
     * @return array
     */
    public function getClassList()
    {
        return $this->_classList;
    }

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
     * @param  string $value
     * @return Zend_Tag_Cloud_Decorator_HtmlTag
     */
    public function setEncoding($value)
    {
        $this->_encoding = (string) $value;
        return $this;
    }

    /**
     * Set the font size unit
     *
     * Possible values are: em, ex, px, in, cm, mm, pt, pc and %
     *
     * @param  string $fontSizeUnit
     * @throws Zend_Tag_Cloud_Decorator_Exception When an invalid fontsize unit is specified
     * @return Zend_Tag_Cloud_Decorator_HtmlTag
     */
    public function setFontSizeUnit($fontSizeUnit)
    {
        if (!in_array($fontSizeUnit, $this->_alloweFontSizeUnits)) {
            #require_once 'Zend/Tag/Cloud/Decorator/Exception.php';
            throw new Zend_Tag_Cloud_Decorator_Exception('Invalid fontsize unit specified');
        }

        $this->_fontSizeUnit = (string) $fontSizeUnit;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve font size unit
     *
     * @return string
     */
    public function getFontSizeUnit()
    {
        return $this->_fontSizeUnit;
    }
     /**
     * Set the HTML tags surrounding the <a> element
     *
     * @param  array $htmlTags
     * @return Zend_Tag_Cloud_Decorator_HtmlTag
     */
    public function setHtmlTags(array $htmlTags)
    {
        $this->_htmlTags = $htmlTags;
        return $this;
    }

    /**
     * Get HTML tags map
     *
     * @return array
     */
    public function getHtmlTags()
    {
        return $this->_htmlTags;
    }

    /**
     * Set maximum font size
     *
     * @param  integer $maxFontSize
     * @throws Zend_Tag_Cloud_Decorator_Exception When fontsize is not numeric
     * @return Zend_Tag_Cloud_Decorator_HtmlTag
     */
    public function setMaxFontSize($maxFontSize)
    {
        if (!is_numeric($maxFontSize)) {
            #require_once 'Zend/Tag/Cloud/Decorator/Exception.php';
            throw new Zend_Tag_Cloud_Decorator_Exception('Fontsize must be numeric');
        }

        $this->_maxFontSize = (int) $maxFontSize;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve maximum font size
     *
     * @return int
     */
    public function getMaxFontSize()
    {
        return $this->_maxFontSize;
    }

    /**
     * Set minimum font size
     *
     * @param  int $minFontSize
     * @throws Zend_Tag_Cloud_Decorator_Exception When fontsize is not numeric
     * @return Zend_Tag_Cloud_Decorator_HtmlTag
     */
    public function setMinFontSize($minFontSize)
    {
        if (!is_numeric($minFontSize)) {
            #require_once 'Zend/Tag/Cloud/Decorator/Exception.php';
            throw new Zend_Tag_Cloud_Decorator_Exception('Fontsize must be numeric');
        }

        $this->_minFontSize = (int) $minFontSize;
        $this->setClassList(null);
        return $this;
    }

    /**
     * Retrieve minimum font size
     *
     * @return int
     */
    public function getMinFontSize()
    {
        return $this->_minFontSize;
    }

    /**
     * Defined by Zend_Tag_Cloud_Decorator_Tag
     *
     * @param  Zend_Tag_ItemList $tags
     * @return array
     */
    public function render(Zend_Tag_ItemList $tags)
    {
        if (null === ($weightValues = $this->getClassList())) {
            $weightValues = range($this->getMinFontSize(), $this->getMaxFontSize());
        }

        $tags->spreadWeightValues($weightValues);

        $result = array();

        $enc = $this->getEncoding();
        foreach ($tags as $tag) {
            if (null === ($classList = $this->getClassList())) {
                $attribute = sprintf('style="font-size: %d%s;"', $tag->getParam('weightValue'), $this->getFontSizeUnit());
            } else {
                $attribute = sprintf('class="%s"', htmlspecialchars($tag->getParam('weightValue'), ENT_COMPAT, $enc));
            }

            $tagHtml = sprintf('<a href="%s" %s>%s</a>', htmlSpecialChars($tag->getParam('url'), ENT_COMPAT, $enc), $attribute, $tag->getTitle());

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

                $tagHtml = sprintf('<%1$s%3$s>%2$s</%1$s>', $htmlTag, $tagHtml, $attributes);
            }

            $result[] = $tagHtml;
        }

        return $result;
    }
}
