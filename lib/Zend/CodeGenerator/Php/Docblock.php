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
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Docblock.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_CodeGenerator_Php_Abstract
 */
#require_once 'Zend/CodeGenerator/Php/Abstract.php';

/**
 * @see Zend_CodeGenerator_Php_Docblock_Tag
 */
#require_once 'Zend/CodeGenerator/Php/Docblock/Tag.php';

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_CodeGenerator_Php_Docblock extends Zend_CodeGenerator_Php_Abstract
{
    /**
     * @var string
     */
    protected $_shortDescription = null;

    /**
     * @var string
     */
    protected $_longDescription = null;

    /**
     * @var array
     */
    protected $_tags = array();

    /**
     * @var string
     */
    protected $_indentation = '';

    /**
     * fromReflection() - Build a docblock generator object from a reflection object
     *
     * @param Zend_Reflection_Docblock $reflectionDocblock
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public static function fromReflection(Zend_Reflection_Docblock $reflectionDocblock)
    {
        $docblock = new self();

        $docblock->setSourceContent($reflectionDocblock->getContents());
        $docblock->setSourceDirty(false);

        $docblock->setShortDescription($reflectionDocblock->getShortDescription());
        $docblock->setLongDescription($reflectionDocblock->getLongDescription());

        foreach ($reflectionDocblock->getTags() as $tag) {
            $docblock->setTag(Zend_CodeGenerator_Php_Docblock_Tag::fromReflection($tag));
        }

        return $docblock;
    }

    /**
     * setShortDescription()
     *
     * @param string $shortDescription
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public function setShortDescription($shortDescription)
    {
        $this->_shortDescription = $shortDescription;
        return $this;
    }

    /**
     * getShortDescription()
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->_shortDescription;
    }

    /**
     * setLongDescription()
     *
     * @param string $longDescription
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public function setLongDescription($longDescription)
    {
        $this->_longDescription = $longDescription;
        return $this;
    }

    /**
     * getLongDescription()
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->_longDescription;
    }

    /**
     * setTags()
     *
     * @param array $tags
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public function setTags(Array $tags)
    {
        foreach ($tags as $tag) {
            $this->setTag($tag);
        }

        return $this;
    }

    /**
     * setTag()
     *
     * @param array|Zend_CodeGenerator_Php_Docblock_Tag $tag
     * @return Zend_CodeGenerator_Php_Docblock
     */
    public function setTag($tag)
    {
        if (is_array($tag)) {
            $tag = new Zend_CodeGenerator_Php_Docblock_Tag($tag);
        } elseif (!$tag instanceof Zend_CodeGenerator_Php_Docblock_Tag) {
            #require_once 'Zend/CodeGenerator/Php/Exception.php';
            throw new Zend_CodeGenerator_Php_Exception(
                'setTag() expects either an array of method options or an '
                . 'instance of Zend_CodeGenerator_Php_Docblock_Tag'
                );
        }

        $this->_tags[] = $tag;
        return $this;
    }

    /**
     * getTags
     *
     * @return array Array of Zend_CodeGenerator_Php_Docblock_Tag
     */
    public function getTags()
    {
        return $this->_tags;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        if (!$this->isSourceDirty()) {
            return $this->_docCommentize($this->getSourceContent());
        }

        $output  = '';
        if (null !== ($sd = $this->getShortDescription())) {
            $output .= $sd . self::LINE_FEED . self::LINE_FEED;
        }
        if (null !== ($ld = $this->getLongDescription())) {
            $output .= $ld . self::LINE_FEED . self::LINE_FEED;
        }

        foreach ($this->getTags() as $tag) {
            $output .= $tag->generate() . self::LINE_FEED;
        }

        return $this->_docCommentize(trim($output));
    }

    /**
     * _docCommentize()
     *
     * @param string $content
     * @return string
     */
    protected function _docCommentize($content)
    {
        $indent = $this->getIndentation();
        $output = $indent . '/**' . self::LINE_FEED;
        $content = wordwrap($content, 80, self::LINE_FEED);
        $lines = explode(self::LINE_FEED, $content);
        foreach ($lines as $line) {
            $output .= $indent . ' * ' . $line . self::LINE_FEED;
        }
        $output .= $indent . ' */' . self::LINE_FEED;
        return $output;
    }

}
