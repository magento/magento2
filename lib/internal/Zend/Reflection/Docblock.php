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
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Docblock.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Reflection_Docblock_Tag
 */
#require_once 'Zend/Reflection/Docblock/Tag.php';

/**
 * @category   Zend
 * @package    Zend_Reflection
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Reflection_Docblock implements Reflector
{
    /**
     * @var Reflector
     */
    protected $_reflector = null;

    /**#@+
     * @var int
     */
    protected $_startLine = null;
    protected $_endLine   = null;
    /**#@-*/

    /**
     * @var string
     */
    protected $_docComment = null;

    /**
     * @var string
     */
    protected $_cleanDocComment = null;

    /**
     * @var string
     */
    protected $_longDescription = null;

    /**
     * @var string
     */
    protected $_shortDescription = null;

    /**
     * @var array
     */
    protected $_tags = array();

    /**
     * Export reflection
     *
     * Reqired by the Reflector interface.
     *
     * @todo   What should this do?
     * @return void
     */
    public static function export()
    {

    }

    /**
     * Serialize to string
     *
     * Required by the Reflector interface
     *
     * @todo   What should this return?
     * @return string
     */
    public function __toString()
    {
        $str = "Docblock [ /* Docblock */ ] {".PHP_EOL.PHP_EOL;
        $str .= "  - Tags [".count($this->_tags)."] {".PHP_EOL;

        foreach($this->_tags AS $tag) {
            $str .= "    ".$tag;
        }

        $str .= "  }".PHP_EOL;
        $str .= "}".PHP_EOL;

        return $str;
    }

    /**
     * Constructor
     *
     * @param Reflector|string $commentOrReflector
     */
    public function __construct($commentOrReflector)
    {
        if ($commentOrReflector instanceof Reflector) {
            $this->_reflector = $commentOrReflector;
            if (!method_exists($commentOrReflector, 'getDocComment')) {
                #require_once 'Zend/Reflection/Exception.php';
                throw new Zend_Reflection_Exception('Reflector must contain method "getDocComment"');
            }
            $docComment = $commentOrReflector->getDocComment();

            $lineCount = substr_count($docComment, "\n");

            $this->_startLine = $this->_reflector->getStartLine() - $lineCount - 1;
            $this->_endLine   = $this->_reflector->getStartLine() - 1;

        } elseif (is_string($commentOrReflector)) {
            $docComment = $commentOrReflector;
        } else {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception(get_class($this) . ' must have a (string) DocComment or a Reflector in the constructor');
        }

        if ($docComment == '') {
            #require_once 'Zend/Reflection/Exception.php';
            throw new Zend_Reflection_Exception('DocComment cannot be empty');
        }

        $this->_docComment = $docComment;
        $this->_parse();
    }

    /**
     * Retrieve contents of docblock
     *
     * @return string
     */
    public function getContents()
    {
        return $this->_cleanDocComment;
    }

    /**
     * Get start line (position) of docblock
     *
     * @return int
     */
    public function getStartLine()
    {
        return $this->_startLine;
    }

    /**
     * Get last line (position) of docblock
     *
     * @return int
     */
    public function getEndLine()
    {
        return $this->_endLine;
    }

    /**
     * Get docblock short description
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->_shortDescription;
    }

    /**
     * Get docblock long description
     *
     * @return string
     */
    public function getLongDescription()
    {
        return $this->_longDescription;
    }

    /**
     * Does the docblock contain the given annotation tag?
     *
     * @param  string $name
     * @return bool
     */
    public function hasTag($name)
    {
        foreach ($this->_tags as $tag) {
            if ($tag->getName() == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve the given docblock tag
     *
     * @param  string $name
     * @return Zend_Reflection_Docblock_Tag|false
     */
    public function getTag($name)
    {
        foreach ($this->_tags as $tag) {
            if ($tag->getName() == $name) {
                return $tag;
            }
        }

        return false;
    }

    /**
     * Get all docblock annotation tags
     *
     * @param string $filter
     * @return array Array of Zend_Reflection_Docblock_Tag
     */
    public function getTags($filter = null)
    {
        if ($filter === null || !is_string($filter)) {
            return $this->_tags;
        }

        $returnTags = array();
        foreach ($this->_tags as $tag) {
            if ($tag->getName() == $filter) {
                $returnTags[] = $tag;
            }
        }
        return $returnTags;
    }

    /**
     * Parse the docblock
     *
     * @return void
     */
    protected function _parse()
    {
        $docComment = $this->_docComment;

        // First remove doc block line starters
        $docComment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docComment);
        $docComment = ltrim($docComment, "\r\n"); // @todo should be changed to remove first and last empty line

        $this->_cleanDocComment = $docComment;

        // Next parse out the tags and descriptions
        $parsedDocComment = $docComment;
        $lineNumber = $firstBlandLineEncountered = 0;
        while (($newlinePos = strpos($parsedDocComment, "\n")) !== false) {
            $lineNumber++;
            $line = substr($parsedDocComment, 0, $newlinePos);

            $matches = array();

            if ((strpos($line, '@') === 0) && (preg_match('#^(@\w+.*?)(\n)(?:@|\r?\n|$)#s', $parsedDocComment, $matches))) {
                $this->_tags[] = Zend_Reflection_Docblock_Tag::factory($matches[1]);
                $parsedDocComment = str_replace($matches[1] . $matches[2], '', $parsedDocComment);
            } else {
                if ($lineNumber < 3 && !$firstBlandLineEncountered) {
                    $this->_shortDescription .= $line . "\n";
                } else {
                    $this->_longDescription .= $line . "\n";
                }

                if ($line == '') {
                    $firstBlandLineEncountered = true;
                }

                $parsedDocComment = substr($parsedDocComment, $newlinePos + 1);
            }

        }

        $this->_shortDescription = rtrim($this->_shortDescription);
        $this->_longDescription  = rtrim($this->_longDescription);
    }
}
